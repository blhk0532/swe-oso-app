<?php

namespace App\Jobs;

use App\Events\PostNummerStatusUpdated;
use App\Models\PostNummer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class ProcessPostNummer implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600; // 1 hour timeout

    public int $tries = 1; // Only try once

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $postNummer
    ) {
        // Set the queue to ensure jobs run in order
        $this->onQueue('postnummer');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[PostNummer {$this->postNummer}] Job starting");

        // Find the PostNummer record
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();

        if (! $record) {
            Log::error("[PostNummer {$this->postNummer}] Record not found");

            return;
        }

        // Get the script path
        $scriptPath = base_path('scripts/post_ort_update.mjs');

        if (! file_exists($scriptPath)) {
            Log::error("Script not found at: {$scriptPath}");
            $record->update(['status' => 'pending', 'is_active' => false]);

            return;
        }

        // PRE-FLIGHT CHECK: Run --onlyTotals first to detect "no direct match" case
        Log::info("[PostNummer {$this->postNummer}] Running pre-flight check (onlyTotals)");
        $checkResult = Process::path(base_path('scripts'))
            ->timeout(120) // 2 minute timeout for quick check
            ->run(['node', 'post_ort_update.mjs', $this->postNummer, '--onlyTotals']);

        if ($checkResult->successful()) {
            $checkOutput = $checkResult->output();
            $checkLines = explode("\n", $checkOutput);
            $noDirectResults = false;

            foreach ($checkLines as $line) {
                $line = trim($line);
                if (preg_match('/^NO_DIRECT_RESULTS=1$/', $line)) {
                    $noDirectResults = true;

                    break;
                }
                if (str_contains(mb_strtolower($line), 'ingen träff på') && str_contains(mb_strtolower($line), 'visar utökat resultat')) {
                    $noDirectResults = true;

                    break;
                }
            }

            if ($noDirectResults) {
                Log::info("[PostNummer {$this->postNummer}] No direct results (extended results only). Marking empty and skipping full scrape.");
                $record->update([
                    'status' => null,
                    'total_count' => null,
                    'is_active' => false,
                    'is_complete' => false,
                ]);
                event(new PostNummerStatusUpdated($record->fresh()));

                return; // Skip full scrape, move to next job
            }
        } else {
            Log::warning("[PostNummer {$this->postNummer}] Pre-flight check failed: {$checkResult->errorOutput()}");
            // Continue with full scrape despite check failure
        }

        // Determine resume state
        $resumePage = (int) ($record->last_processed_page ?? 0);
        $resumeCount = (int) ($record->processed_count ?? 0);
        Log::info("[PostNummer {$this->postNummer}] Resume state page={$resumePage} count={$resumeCount}");

        // Update status to running (keep existing progress if resuming)
        $record->update([
            'status' => 'running',
            'is_active' => true,
            'progress' => $record->progress ?? 0,
            // Ensure phone & house default to 0 when starting
            'phone' => $record->phone ?? 0,
            'house' => $record->house ?? 0,
        ]);

        event(new PostNummerStatusUpdated($record));

        Log::info("[PostNummer {$this->postNummer}] Invoking script post_ort_update.mjs");

        // Run the script with the post nummer as the search query (with resume args)
        $currentTotalCount = (int) ($record->total_count ?? 0);
        $currentCount = (int) max(0, $resumeCount);
        $result = Process::path(base_path('scripts'))
            ->timeout($this->timeout)
            ->run(['node', 'post_ort_update.mjs', $this->postNummer, '--startPage', (string) $resumePage, '--startIndex', (string) $resumeCount], function (string $type, string $buffer) use ($record, &$currentTotalCount, &$currentCount) {
                // Parse output in real-time to update progress
                $lines = explode("\n", $buffer);
                $phoneCount = (int) ($record->phone ?? 0);
                $houseCount = (int) ($record->house ?? 0);

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) {
                        continue;
                    }

                    Log::info("[PostNummer {$record->post_nummer}] OUT: {$line}");

                    // Extract total results count only (avoid matching "Total pages")
                    if (
                        // Match "Total results: 402"
                        preg_match('/^Total\s+results\s*:\s*(\d+)/i', $line, $matches)
                        ||
                        // Match "Found 402 results" but NOT "on page"
                        preg_match('/^Found\s+(\d+)\s+results(?!\s+on\s+page)/i', $line, $matches)
                    ) {
                        $totalCount = (int) $matches[1];
                        $currentTotalCount = $totalCount;
                        $record->update(['total_count' => $totalCount]);
                        Log::info("[PostNummer {$record->post_nummer}] total_count={$totalCount}");
                    }

                    // Incremental progress based on extracted items per page
                    if (preg_match('/^\[Page\s+\d+\]\s+Extracted\s+(\d+)\/(\d+)/i', $line, $matches)) {
                        // Increase global count by 1 when an item is extracted
                        $currentCount = $currentCount + 1;
                        $updates = [
                            'count' => $currentCount,
                            'processed_count' => $currentCount,
                        ];
                        if ($currentTotalCount > 0) {
                            $updates['progress'] = round(($currentCount / $currentTotalCount) * 100, 2);
                        }
                        $record->update($updates);
                        $progressText = array_key_exists('progress', $updates) ? (string) $updates['progress'] : 'n/a';
                        Log::info("[PostNummer {$record->post_nummer}] progress={$progressText} ({$currentCount}/" . ($currentTotalCount ?: 'unknown') . ')');
                        event(new PostNummerStatusUpdated($record->fresh()));
                    }

                    // Track page lines: "Page 3:" or "Page 3 of 17"
                    if (preg_match('/^Page\s+(\d+)(?:\s+of\s+\d+)?/i', $line, $pm)) {
                        $pageNo = (int) $pm[1];
                        $record->update(['last_processed_page' => $pageNo]);
                        Log::info("[PostNummer {$record->post_nummer}] last_processed_page={$pageNo}");
                        event(new PostNummerStatusUpdated($record->fresh()));
                    }

                    // Aggregate phones and houses on-the-fly if script emits detail lines
                    // Expected summary lines at end: "Phones: X" and "Houses: Y"
                    if (preg_match('/^Phones:\s*(\d+)/i', $line, $m)) {
                        $phoneCount = (int) $m[1];
                        $record->update(['phone' => $phoneCount]);
                        Log::info("[PostNummer {$record->post_nummer}] phone={$phoneCount}");
                        event(new PostNummerStatusUpdated($record->fresh()));
                    }
                    if (preg_match('/^Houses:\s*(\d+)/i', $line, $m)) {
                        $houseCount = (int) $m[1];
                        $record->update(['house' => $houseCount]);
                        Log::info("[PostNummer {$record->post_nummer}] house={$houseCount}");
                        event(new PostNummerStatusUpdated($record->fresh()));
                    }
                }
            });

        if ($result->successful()) {
            Log::info("[PostNummer {$this->postNummer}] Script completed successfully");

            // Reload record to get latest values
            $record = $record->fresh();

            $total = (int) ($record->total_count ?? 0);
            $done = (int) ($record->count ?? 0);
            $computedProgress = $total > 0 ? (int) min(100, floor(($done / $total) * 100)) : (int) ($record->progress ?? 0);

            // Only mark as complete if we've actually processed all known results
            if ($total > 0 && $done >= $total) {
                $record->update([
                    'status' => 'complete',
                    'progress' => 100,
                    'is_active' => false,
                    'is_complete' => true,
                ]);
            } else {
                // Not fully complete – keep record pending for resume, don't force 100%
                $record->update([
                    'status' => 'pending',
                    'progress' => $computedProgress,
                    'is_active' => false,
                    'is_complete' => false,
                ]);
            }

            event(new PostNummerStatusUpdated($record));
        } else {
            Log::error("[PostNummer {$this->postNummer}] Script failed: {$result->errorOutput()}");

            // Update status back to pending
            $record->update([
                'status' => 'pending',
                'is_active' => false,
                'progress' => 0,
            ]);

            event(new PostNummerStatusUpdated($record));

            throw new Exception("Script execution failed: {$result->errorOutput()}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[PostNummer {$this->postNummer}] Job failed: {$exception->getMessage()}");

        // Update record status on failure
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();
        if ($record) {
            $record->update([
                'status' => 'pending',
                'is_active' => false,
                'progress' => 0,
            ]);

            event(new PostNummerStatusUpdated($record));
        }
    }
}
