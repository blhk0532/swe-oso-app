<?php

namespace App\Jobs;

use App\Models\PostNummerCheck;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RunPostNummerChecksJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PostNummerCheck $postNummerCheck,
        public ?string $customBatchId = null
    ) {
        $this->onQueue('postnummer-checks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $postNummer = str_replace(' ', '', $this->postNummerCheck->post_nummer);

            Log::info("Starting postnummer checks job for: {$postNummer}");

            // Create or get batch
            if ($this->customBatchId) {
                $batch = DB::table('job_batches')->where('id', $this->customBatchId)->first();
            } else {
                $batch = $this->batch();
            }

            // Run Hitta script
            $hittaScriptPath = base_path('scripts/hitta_check_counts.mjs');
            $hittaCommand = "node {$hittaScriptPath} \"{$postNummer}\"";

            Log::info("Executing Hitta command: {$hittaCommand}");
            $hittaOutput = shell_exec($hittaCommand);
            Log::info('Hitta script completed', ['output' => $hittaOutput]);

            // Run Ratsit script
            $ratsitScriptPath = base_path('scripts/ratsit_check_counts.mjs');
            $ratsitCommand = "node {$ratsitScriptPath} \"{$postNummer}\"";

            Log::info("Executing Ratsit command: {$ratsitCommand}");
            $ratsitOutput = shell_exec($ratsitCommand);
            Log::info('Ratsit script completed', ['output' => $ratsitOutput]);

            // Refresh the model to get updated values from database
            $this->postNummerCheck->refresh();

            // Update status to complete
            $this->postNummerCheck->update(['status' => 'complete']);

            Log::info('Postnummer checks job completed successfully', [
                'post_nummer' => $postNummer,
                'hitta_personer' => $this->postNummerCheck->hitta_personer_total,
                'hitta_foretag' => $this->postNummerCheck->hitta_foretag_total,
                'ratsit_personer' => $this->postNummerCheck->ratsit_personer_total,
                'ratsit_foretag' => $this->postNummerCheck->ratsit_foretag_total,
            ]);

        } catch (Exception $e) {
            Log::error('Postnummer checks failed', [
                'post_nummer' => $this->postNummerCheck->post_nummer,
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
        return 'Postnummer: ' . $this->postNummerCheck->post_nummer;
    }

    /**
     * Prepare the instance for serialization.
     */
    public function __serialize(): array
    {
        $properties = get_object_vars($this);

        // Add custom name to serialized data
        $properties['name'] = 'Postnummer: ' . $this->postNummerCheck->post_nummer;

        return array_keys($properties);
    }

    /**
     * Dispatch the job with a custom name.
     */
    public static function dispatchWithName(PostNummerCheck $postNummerCheck): mixed
    {
        $job = new static($postNummerCheck);

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
            'postnummer:' . $this->postNummerCheck->post_nummer,
        ];
    }
}
