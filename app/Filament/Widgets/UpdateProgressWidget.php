<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UpdateProgressWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    public function getPollingInterval(): ?string
    {
        return '2s';
    }

    protected function getStats(): array
    {
        $progress = Cache::get('update_post_nummer_progress');

        if (! $progress) {
            return [];
        }

        $stats = [];

        // Status stat
        $stats[] = Stat::make('Update Status', ucfirst($progress['status'] ?? 'Unknown'))
            ->description($progress['message'] ?? '')
            ->color(match ($progress['status'] ?? '') {
                'running' => 'warning',
                'completed' => 'success',
                'failed' => 'danger',
                default => 'gray',
            });

        // Progress stat
        if (isset($progress['percentage'])) {
            $stats[] = Stat::make('Progress', $progress['percentage'] . '%')
                ->description(
                    isset($progress['processed'], $progress['total'])
                    ? "{$progress['processed']} / {$progress['total']} records"
                    : ''
                )
                ->color('primary');
        }

        // Updated stat
        if (isset($progress['updated'])) {
            $stats[] = Stat::make('Updated', number_format($progress['updated']))
                ->description('Records updated')
                ->color('success');
        }

        // Skipped stat
        if (isset($progress['skipped'])) {
            $stats[] = Stat::make('Skipped', number_format($progress['skipped']))
                ->description('Records not found')
                ->color('gray');
        }

        return $stats;
    }
}
