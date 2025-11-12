<?php

namespace App\Jobs;

use App\Models\PostNummer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class RunHittaScript implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600; // 1 hour timeout

    public int $tries = 3; // Retry up to 3 times on failure

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $postNummer
    ) {
        // Set the queue to ensure jobs run in order
        $this->onQueue('hitta');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[Hitta Script {$this->postNummer}] Job starting");

        // Find the PostNummer record
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();

        if (! $record) {
            Log::error("[Hitta Script {$this->postNummer}] Record not found");

            return;
        }

        try {
            // Update status to running
            $record->update([
                'status' => 'running_hitta',
                'is_active' => true,
            ]);

            // Get the script path
            $scriptPath = base_path('scripts/hitta.mjs');

            Log::info("[Hitta Script {$this->postNummer}] Script path: " . $scriptPath);
            Log::info("[Hitta Script {$this->postNummer}] Working directory: " . base_path('scripts'));

            // Run the script from the scripts directory
            $process = Process::path(base_path('scripts'))
                ->timeout(3600)
                ->run(['node', 'hitta.mjs', $this->postNummer, '--api-url', config('app.url')]);

            Log::info("[Hitta Script {$this->postNummer}] Process exit code: " . $process->exitCode());
            Log::info("[Hitta Script {$this->postNummer}] Process output: " . $process->output());
            if ($process->errorOutput()) {
                Log::error("[Hitta Script {$this->postNummer}] Process error output: " . $process->errorOutput());
            }

            if ($process->successful()) {
                Log::info("[Hitta Script {$this->postNummer}] Script completed successfully");

                // Update record status
                $record->update([
                    'status' => 'Checked',
                    'is_active' => false,
                ]);
            } else {
                Log::error("[Hitta Script {$this->postNummer}] Script failed: " . $process->errorOutput());

                // Update record status
                $record->update([
                    'status' => 'failed_hitta',
                    'is_active' => false,
                ]);

                throw new Exception('Hitta script failed: ' . $process->errorOutput());
            }

        } catch (Throwable $e) {
            Log::error("[Hitta Script {$this->postNummer}] Exception: " . $e->getMessage());

            // Update record status
            $record->update([
                'status' => 'error_hitta',
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
        Log::error("[Hitta Script {$this->postNummer}] Job failed: " . $exception->getMessage());

        // Find the PostNummer record and update status
        $record = PostNummer::where('post_nummer', $this->postNummer)->first();
        if ($record) {
            $record->update([
                'status' => 'failed_hitta',
                'is_active' => false,
            ]);
        }
    }
}
