<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdatePostNummerFromSweden implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public $timeout = 3600; // 1 hour timeout

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Set initial progress
        Cache::put('update_post_nummer_progress', [
            'status' => 'running',
            'total' => 0,
            'updated' => 0,
            'skipped' => 0,
            'message' => 'Starting update...',
        ], 3600);

        // Get all records from sweden table
        $swedenData = DB::table('sweden')
            ->select('post_nummer', 'post_ort', 'post_lan')
            ->get();

        $total = $swedenData->count();
        $updated = 0;
        $skipped = 0;

        Cache::put('update_post_nummer_progress', [
            'status' => 'running',
            'total' => $total,
            'updated' => 0,
            'skipped' => 0,
            'message' => "Processing {$total} records...",
        ], 3600);

        foreach ($swedenData as $index => $data) {
            $result = DB::table('post_nummer')
                ->where('post_nummer', $data->post_nummer)
                ->update([
                    'post_ort' => $data->post_ort,
                    'post_lan' => $data->post_lan,
                ]);

            if ($result > 0) {
                $updated++;
            } else {
                $skipped++;
            }

            // Update progress every 100 records
            if (($index + 1) % 100 === 0 || ($index + 1) === $total) {
                Cache::put('update_post_nummer_progress', [
                    'status' => 'running',
                    'total' => $total,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'processed' => $index + 1,
                    'percentage' => round((($index + 1) / $total) * 100, 2),
                    'message' => 'Processed ' . ($index + 1) . " of {$total} records...",
                ], 3600);
            }
        }

        // Set completion status
        Cache::put('update_post_nummer_progress', [
            'status' => 'completed',
            'total' => $total,
            'updated' => $updated,
            'skipped' => $skipped,
            'processed' => $total,
            'percentage' => 100,
            'message' => "Completed! Updated {$updated} records, skipped {$skipped} records.",
        ], 3600);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Cache::put('update_post_nummer_progress', [
            'status' => 'failed',
            'message' => 'Update failed: ' . $exception->getMessage(),
        ], 3600);
    }
}
