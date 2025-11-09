<?php

namespace App\Jobs;

use App\Events\PostNummerStatusUpdated;
use App\Models\PostNummer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class CheckHittaTotals implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600; // 10 minutes

    public int $tries = 1;

    public function __construct(public string $postNummer)
    {
        $this->onQueue('postnummer');
    }

    public function handle(): void
    {
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();
        if (! $record) {
            Log::warning("[CheckHittaTotals {$this->postNummer}] Record not found");

            return;
        }

        Log::info("[CheckHittaTotals {$this->postNummer}] Starting totals check");

        $scriptPath = base_path('scripts/post_ort_update.mjs');
        if (! file_exists($scriptPath)) {
            Log::error("[CheckHittaTotals {$this->postNummer}] Script missing at {$scriptPath}");

            return;
        }

        $result = Process::path(base_path('scripts'))
            ->timeout($this->timeout)
            ->run(['node', 'post_ort_update.mjs', $this->postNummer, '--onlyTotals']);

        if (! $result->successful()) {
            Log::error("[CheckHittaTotals {$this->postNummer}] Script failed: {$result->errorOutput()}");

            return;
        }

        $output = $result->output();
        $lines = explode("\n", $output);
        $total = null;
        $noDirectResults = false;
        foreach ($lines as $line) {
            $line = trim($line);
            // Machine-readable flag from script for extended results (no direct match)
            if (preg_match('/^NO_DIRECT_RESULTS=1$/', $line)) {
                $noDirectResults = true;

                break;
            }
            // Fallback: detect Swedish phrase if script didn't emit the flag
            if (str_contains(mb_strtolower($line), 'ingen träff på') && str_contains(mb_strtolower($line), 'visar utökat resultat')) {
                $noDirectResults = true;

                break;
            }
            if (preg_match('/^Total\s+results\s*:\s*(\d+)/i', $line, $m)) {
                $total = (int) $m[1];

                break;
            }
            if (preg_match('/^Found\s+(\d+)\s+results(?!\s+on\s+page)/i', $line, $m)) {
                $total = (int) $m[1];

                break;
            }
        }

        // Handle the extended results case: clear status and total_count (display as — in UI)
        if ($noDirectResults) {
            $record->update([
                'status' => null,
                'total_count' => null,
                // Keep existing count/progress as-is; ensure flags are not active/complete
                'is_active' => false,
                'is_complete' => false,
            ]);

            Log::info("[CheckHittaTotals {$this->postNummer}] No direct results (extended results shown). Cleared status and total_count.");
            event(new PostNummerStatusUpdated($record->fresh()));

            return;
        }

        if ($total !== null) {
            // Reconcile totals with current progress & status
            $done = (int) ($record->count ?? 0);
            $updates = [
                'total_count' => $total,
            ];

            if ($total > 0) {
                $computed = (int) min(100, max(0, floor(($done / $total) * 100)));
                $updates['progress'] = $computed;
            }

            // If previously marked complete but counts disagree, revert to pending
            if ($record->status === 'complete' && $total > 0 && $done < $total) {
                $updates['status'] = 'pending';
                $updates['is_complete'] = false;
                $updates['is_active'] = false;
            }

            // If counts show completion, ensure status is complete
            if ($total > 0 && $done >= $total) {
                $updates['status'] = 'complete';
                $updates['is_complete'] = true;
                $updates['is_active'] = false;
                $updates['progress'] = 100;
            }

            $record->update($updates);

            Log::info("[CheckHittaTotals {$this->postNummer}] Reconciled totals: total={$total}, count={$done}, updates=" . json_encode(array_keys($updates)));
            event(new PostNummerStatusUpdated($record->fresh()));
        } else {
            Log::warning("[CheckHittaTotals {$this->postNummer}] No total results found in output");
        }
    }
}
