<?php

namespace App\Filament\Widgets;

use App\Models\PostNummer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpdateProgressWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    public function getPollingInterval(): ?string
    {
        return '2s';
    }

    protected function getStats(): array
    {
        // Get real-time data from post_nummer table
        $runningRecords = PostNummer::where('status', 'running')->get();
        $totalRecords = PostNummer::count();
        $updatedRecords = PostNummer::where('status', 'complete')->count();
        $pendingRecords = PostNummer::where('status', 'pending')
            ->orWhereNull('status')
            ->count();

        // If no running records, show minimal state
        if ($runningRecords->isEmpty()) {
            return [
                Stat::make('Status', 'Idle')
                    ->description('No jobs running')
                    ->color('gray'),

                Stat::make('Total', number_format($totalRecords))
                    ->description('Records')
                    ->color('gray'),

                Stat::make('Updated', number_format($updatedRecords))
                    ->description('Records updated')
                    ->color('success'),

                Stat::make('Pending', number_format($pendingRecords))
                    ->description('Records pending')
                    ->color('warning'),
            ];
        }

        // Build running post nummers list (show up to 5)
        $runningCount = $runningRecords->count();
        $runningList = $runningRecords->take(5)->pluck('post_nummer')->toArray();
        $runningDesc = implode(', ', $runningList);
        if ($runningCount > count($runningList)) {
            $runningDesc .= ' (+' . ($runningCount - count($runningList)) . ' more)';
        }

        // Calculate aggregated progress across all running jobs
        $totalCount = $runningRecords->sum('total_count');
        $currentCount = $runningRecords->sum('count');
        $avgProgress = $runningRecords->avg('progress') ?? 0;

        $stats = [];

        // 1) Status
        $stats[] = Stat::make('Status', 'Running')
            ->description($runningDesc)
            ->color('warning');

        // 2) Queue (average progress + total processed/total)
        $percentage = round($avgProgress) . '%';
        $queueDesc = $totalCount > 0
            ? number_format($currentCount) . ' / ' . number_format($totalCount)
            : 'Calculating...';

        $stats[] = Stat::make('Queue', $percentage)
            ->description($queueDesc)
            ->color('primary');

        // 3) Updated
        $stats[] = Stat::make('Updated', number_format($updatedRecords))
            ->description('Records updated')
            ->color('success');

        // 4) Total
        $stats[] = Stat::make('Total', number_format($totalRecords))
            ->description('Records')
            ->color('gray');

        // 5) Skipped (optional - records that failed or were skipped)
        $failedRecords = PostNummer::where('status', 'failed')->count();
        if ($failedRecords > 0) {
            $stats[] = Stat::make('Skipped', number_format($failedRecords))
                ->description('Records not found')
                ->color('gray');
        }

        return $stats;
    }
}
