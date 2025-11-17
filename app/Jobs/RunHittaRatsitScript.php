<?php

namespace App\Jobs;

use App\Models\PostNummer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class RunHittaRatsitScript implements ShouldQueue
{
    use Queueable;

    public int $timeout = 7200; // 2 hour timeout (longer for combined script)

    public int $tries = 3; // Retry up to 3 times on failure

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $postNummer
    ) {
        // Set the queue to ensure jobs run in order
        $this->onQueue('hitta_ratsit');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[Hitta+Ratsit Script {$this->postNummer}] Job starting");

        // Find the PostNummer record
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();

        if (! $record) {
            Log::error("[Hitta+Ratsit Script {$this->postNummer}] Record not found");

            return;
        }

        try {
            // Update status to running
            $record->update([
                'status' => 'running_hitta_ratsit',
                'is_active' => true,
            ]);

            // Get the script path - use post_ort_update.mjs which saves to both hitta_data and ratsit_data
            $scriptPath = base_path('scripts/post_ort_update.mjs');

            // Run the script from the scripts directory
            $process = Process::path(base_path('scripts'))
                ->timeout(7200)
                ->run(['node', 'post_ort_update.mjs', $this->postNummer, '--api-url', config('app.url')]);

            if ($process->successful()) {
                Log::info("[Hitta+Ratsit Script {$this->postNummer}] Script completed successfully");

                // Update record status
                $record->update([
                    'status' => 'completed_hitta_ratsit',
                    'is_active' => false,
                ]);
            } else {
                Log::error("[Hitta+Ratsit Script {$this->postNummer}] Script failed: " . $process->errorOutput());

                // Update record status
                $record->update([
                    'status' => 'failed_hitta_ratsit',
                    'is_active' => false,
                ]);

                throw new Exception('Hitta+Ratsit script failed: ' . $process->errorOutput());
            }

        } catch (Throwable $e) {
            Log::error("[Hitta+Ratsit Script {$this->postNummer}] Exception: " . $e->getMessage());

            // Update record status
            $record->update([
                'status' => 'error_hitta_ratsit',
                'is_active' => false,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[Hitta+Ratsit Script {$this->postNummer}] Job failed: " . $exception->getMessage());

        // Find the PostNummer record and update status
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();
        if ($record) {
            $record->update([
                'status' => 'failed_hitta_ratsit',
                'is_active' => false,
            ]);
        }
    }
}
