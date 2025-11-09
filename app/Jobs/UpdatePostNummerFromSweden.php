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
        // Get all records from sweden table
        $swedenData = DB::table('sweden')
            ->select('post_nummer', 'post_ort', 'post_lan')
            ->get();

        $total = $swedenData->count();

        // Set initial progress with total count
        Cache::put('update_post_nummer_progress', [
            'status' => 'running',
            'total' => $total,
            'updated' => 0,
            'skipped' => 0,
            'message' => "Processing {$total} records...",
        ], 3600);

        $updated = 0;
        $skipped = 0;

        foreach ($swedenData as $index => $data) {
            $result = DB::table('post_nummer')
                ->where('post_nummer', $data->post_nummer)
                ->update([
                    'post_ort' => $data->post_ort,
                    'post_lan' => $data->post_lan,
                    'total_count' => $total,
                    'count' => $index + 1,
                    'status' => 'running',
                    'is_active' => true,
                    'progress' => round((($index + 1) / $total) * 100, 2),
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

        // Mark all updated records as complete
        DB::table('post_nummer')
            ->whereNotNull('count')
            ->where('count', '>', 0)
            ->update([
                'status' => 'complete',
                'is_active' => false,
                'is_complete' => true,
            ]);
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
