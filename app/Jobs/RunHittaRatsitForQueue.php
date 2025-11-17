<?php

namespace App\Jobs;

use App\Models\HittaQueue;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class RunHittaRatsitForQueue implements ShouldQueue
{
    use Queueable;

    public int $timeout = 7200; // 2 hour timeout (longer for combined script)

    public int $tries = 3; // Retry up to 3 times on failure

    /**
     * Create a new job instance.
     */
    public function __construct(public HittaQueue $hittaQueue)
    {
        // Use the dedicated 'filament' queue for combined scrapers
        $this->onQueue('filament');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $record = $this->hittaQueue->fresh();

        if (! $record) {
            return;
        }

        Log::info("[Hitta+Ratsit Queue {$record->post_nummer}] Job starting");

        // Update status to running
        $record->update([
            'personer_status' => 'running_hitta_ratsit',
            'is_active' => true,
        ]);

        // Get the script path
        $scriptPath = base_path('scripts/hitta_ratsit.mjs');

        Log::info("[Hitta+Ratsit Queue {$record->post_nummer}] Script path: " . $scriptPath);

        // Run the script
        $process = Process::path(base_path('scripts'))
            ->timeout(7200)
            ->run(['node', 'hitta_ratsit.mjs', $record->post_nummer, '--api-url', config('app.url')]);

        Log::info("[Hitta+Ratsit Queue {$record->post_nummer}] Process exit code: " . $process->exitCode());
        Log::info("[Hitta+Ratsit Queue {$record->post_nummer}] Process output: " . $process->output());
        if ($process->errorOutput()) {
            Log::error("[Hitta+Ratsit Queue {$record->post_nummer}] Process error output: " . $process->errorOutput());
        }

        if ($process->successful()) {
            Log::info("[Hitta+Ratsit Queue {$record->post_nummer}] Script completed successfully");

            // Update record status
            $record->update([
                'personer_status' => 'complete',
                'personer_scraped' => true,
                'is_active' => false,
            ]);
        } else {
            Log::error("[Hitta+Ratsit Queue {$record->post_nummer}] Script failed: " . $process->errorOutput());

            // Update record status
            $record->update([
                'personer_status' => 'failed',
                'is_active' => false,
            ]);

            throw new Exception('Hitta+Ratsit queue script failed: ' . $process->errorOutput());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[Hitta+Ratsit Queue {$this->hittaQueue->post_nummer}] Job failed: " . $exception->getMessage());

        // Find the record and update status
        $record = $this->hittaQueue->fresh();
        if ($record) {
            $record->update([
                'personer_status' => 'failed',
                'is_active' => false,
            ]);
        }
    }
}
