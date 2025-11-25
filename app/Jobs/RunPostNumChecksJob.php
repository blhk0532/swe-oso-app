<?php

namespace App\Jobs;

use App\Models\PostNummer;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RunPostNumChecksJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * The postnummer ID.
     */
    protected $postNummerId;

    /**
     * Custom batch ID.
     */
    // public ?string $customBatchId = null;

    /**
     * The postnummer model instance.
     */
    public ?PostNummer $postNummerCheck = null;

    /**
     * Create a new job instance.
     */
    public function __construct($postNummerId)
    {
        $this->postNummerId = $postNummerId;
        $this->onQueue('postnummer-checks');
    }

    /**
     * Serialize the job.
     */
    public function __serialize(): array
    {
        return [
            'postNummerId' => $this->postNummerId,
        ];
    }

    /**
     * Unserialize the job.
     */
    public function __unserialize(array $data): void
    {
        $this->postNummerId = $data['postNummerId'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // DEBUG: Log current file and line
            Log::info('DEBUG: Job handle() called in file: ' . __FILE__ . ' at line: ' . __LINE__);

            // Load the model from database
            if (! $this->postNummerId) {
                throw new Exception('PostNummer ID is not set');
            }

            $postNummerCheck = PostNummer::find($this->postNummerId);

            if (! $postNummerCheck) {
                throw new Exception("PostNummer with ID {$this->postNummerId} not found");
            }

            $postNummer = str_replace(' ', '', $postNummerCheck->post_nummer);

            Log::info("Starting postnummer checks job for: {$postNummer}");

            // Create or get batch
            $batch = $this->batch();

            // Run Hitta script
            $hittaScriptPath = base_path('jobs/hitta_check_counts.mjs');
            $hittaCommand = "node {$hittaScriptPath} \"{$postNummer}\"";

            Log::info("Executing Hitta command: {$hittaCommand}");
            $hittaOutput = shell_exec($hittaCommand);
            Log::info('Hitta script completed', ['output' => $hittaOutput]);

            // Run Ratsit script (if it exists)
            $ratsitScriptPath = base_path('jobs/ratsit_check_counts.mjs');
            if (file_exists($ratsitScriptPath)) {
                $ratsitCommand = "node {$ratsitScriptPath} \"{$postNummer}\"";
                Log::info("Executing Ratsit command: {$ratsitCommand}");
                $ratsitOutput = shell_exec($ratsitCommand);
                Log::info('Ratsit script completed', ['output' => $ratsitOutput]);
            } else {
                Log::info('Ratsit script not found, skipping');
            }

            // Refresh the model to get updated values from database
            $postNummerCheck->refresh();

            // Update status to complete
            $postNummerCheck->update(['status' => 'complete']);

            Log::info('Postnummer checks job completed successfully', [
                'post_nummer' => $postNummer,
                'hitta_personer_total' => $postNummerCheck->hitta_personer_total,
                'hitta_foretag_total' => $postNummerCheck->hitta_foretag_total,
                'ratsit_personer_total' => $postNummerCheck->ratsit_personer_total,
                'ratsit_foretag_total' => $postNummerCheck->ratsit_foretag_total,
            ]);

        } catch (Exception $e) {
            Log::error('Postnummer checks failed', [
                'post_nummer_id' => $this->postNummerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->fail($e);
        }
    }

    /**
     * Get the display name for the job.
     */
    public function displayName(): string
    {
        $postNummer = PostNummer::find($this->postNummerId);

        return 'Postnummer: ' . ($postNummer ? $postNummer->post_nummer : $this->postNummerId);
    }

    /**
     * Dispatch the job with a custom name.
     */
    public static function dispatchWithName(PostNummer $postNummerCheck): mixed
    {
        $job = new static($postNummerCheck->id);

        // Set the job name before dispatching
        $job->name = 'Postnummer: ' . $postNummerCheck->post_nummer;

        return dispatch($job);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'postnummer-check',
            'postnummer:' . $this->postNummerId,
        ];
    }
}
