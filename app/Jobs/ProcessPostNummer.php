<?php

namespace App\Jobs;

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
        Log::info("Starting ProcessPostNummer job for: {$this->postNummer}");

        // Find the PostNummer record
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();

        if (! $record) {
            Log::error("Post nummer {$this->postNummer} not found");

            return;
        }

        // Update status to running
        $record->update([
            'status' => 'running',
            'is_active' => true,
        ]);

        Log::info("Running post_ort_update.mjs for post nummer: {$this->postNummer}");

        // Get the script path
        $scriptPath = base_path('scripts/post_ort_update.mjs');

        if (! file_exists($scriptPath)) {
            Log::error("Script not found at: {$scriptPath}");
            $record->update(['status' => 'pending', 'is_active' => false]);

            return;
        }

        // Run the script with the post nummer as the search query
        $result = Process::path(base_path('scripts'))
            ->timeout($this->timeout)
            ->run(['node', 'post_ort_update.mjs', $this->postNummer]);

        if ($result->successful()) {
            Log::info("Script completed successfully for: {$this->postNummer}");

            // Update status to complete
            $record->update([
                'status' => 'complete',
                'total_count' => $this->extractTotalCountFromOutput($result->output(), $record->total_count),
            ]);
        } else {
            Log::error("Script failed for {$this->postNummer}: {$result->errorOutput()}");

            // Update status back to pending
            $record->update([
                'status' => 'pending',
                'is_active' => false,
            ]);

            throw new Exception("Script execution failed: {$result->errorOutput()}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("ProcessPostNummer job failed for {$this->postNummer}: {$exception->getMessage()}");

        // Update record status on failure
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();
        if ($record) {
            $record->update([
                'status' => 'pending',
                'is_active' => false,
            ]);
        }
    }

    /**
     * Attempt to parse a total count value from the script output so the table can reflect it without manual refresh.
     */
    protected function extractTotalCountFromOutput(string $output, ?int $current): ?int
    {
        // Example heuristic: look for a line like Total: 123 or total_count=123
        if (preg_match('/Total(?:\s*Count)?\s*[:=]\s*(\d+)/i', $output, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/total_count\s*=\s*(\d+)/i', $output, $m)) {
            return (int) $m[1];
        }

        return $current; // fallback to existing value
    }
}
