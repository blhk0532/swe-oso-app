<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor {--queue=postnummer-checks : The queue to monitor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue status and show pending jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->option('queue');

        $this->info("Monitoring queue: {$queue}");
        $this->newLine();

        // Get job counts
        $pendingJobs = DB::table('jobs')
            ->where('queue', $queue)
            ->where('attempts', 0)
            ->count();

        $failedJobs = DB::table('failed_jobs')
            ->where('queue', $queue)
            ->count();

        $totalJobs = DB::table('jobs')
            ->where('queue', $queue)
            ->count();

        // Display stats
        $this->table(
            ['Status', 'Count'],
            [
                ['Pending Jobs', $pendingJobs],
                ['Total in Queue', $totalJobs],
                ['Failed Jobs', $failedJobs],
            ]
        );

        // Show recent jobs
        if ($totalJobs > 0) {
            $this->newLine();
            $this->info('Recent Jobs:');

            $recentJobs = DB::table('jobs')
                ->where('queue', $queue)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'attempts', 'created_at']);

            foreach ($recentJobs as $job) {
                $status = $job->attempts > 0 ? "Attempt {$job->attempts}" : 'Pending';
                $this->line("  Job #{$job->id} - {$status} - Created: " . date('Y-m-d H:i:s', $job->created_at));
            }
        }

        return Command::SUCCESS;
    }
}
