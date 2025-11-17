<?php

namespace App\Filament\Resources\Jobs\Widgets;

use App\Models\Job;
use App\Models\JobBatch;
use Exception;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class QueueMonitorWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        // Get queue statistics
        $pendingJobs = Job::count();
        $failedJobs = DB::table('failed_jobs')->count();
        $completedToday = DB::table('jobs')
            ->whereRaw('DATE(FROM_UNIXTIME(created_at)) = CURDATE()')
            ->count();

        // Check if queue worker is running
        $queueWorkerRunning = $this->isQueueWorkerRunning();

        // Get batch statistics
        $activeBatches = JobBatch::where('pending_jobs', '>', 0)->count();
        $completedBatches = JobBatch::where('pending_jobs', 0)->where('failed_jobs', 0)->count();

        return [
            Stat::make('Queue Worker Status', $queueWorkerRunning ? 'Running' : 'Stopped')
                ->description($queueWorkerRunning ? 'Processing jobs' : 'No workers active')
                ->descriptionIcon($queueWorkerRunning ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->color($queueWorkerRunning ? 'success' : 'danger')
                ->chart($this->getRecentJobsChart()),

            Stat::make('Pending Jobs', $pendingJobs)
                ->description('Jobs waiting to be processed')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingJobs > 0 ? 'info' : 'gray')
                ->chart($this->getRecentJobsChart()),

            Stat::make('Failed Jobs', $failedJobs)
                ->description('Jobs that failed processing')
                ->descriptionIcon($failedJobs > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($failedJobs > 0 ? 'danger' : 'success'),

            Stat::make('Completed Today', $completedToday)
                ->description('Jobs processed today')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Active Batches', $activeBatches)
                ->description('Job batches in progress')
                ->descriptionIcon('heroicon-o-queue-list')
                ->color($activeBatches > 0 ? 'info' : 'gray'),

            Stat::make('Completed Batches', $completedBatches)
                ->description('Finished job batches')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }

    protected function isQueueWorkerRunning(): bool
    {
        // Check if queue:work process is running
        try {
            $output = [];
            $return_var = 0;
            exec('ps aux | grep "queue:work" | grep -v grep', $output, $return_var);

            return count($output) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getRecentJobsChart(): array
    {
        // Get job counts for the last 7 time periods
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $count = DB::table('jobs')
                ->whereRaw('FROM_UNIXTIME(created_at) >= DATE_SUB(NOW(), INTERVAL ? HOUR)', [$i + 1])
                ->whereRaw('FROM_UNIXTIME(created_at) < DATE_SUB(NOW(), INTERVAL ? HOUR)', [$i])
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
