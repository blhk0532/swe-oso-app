<?php

namespace App\Filament\Resources\Jobs\Pages;

use App\Filament\Resources\Jobs\JobResource;
use App\Filament\Resources\Jobs\Widgets\QueueMonitorWidget;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startWorker')
                ->label('Start Queue Worker')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Start Queue Worker')
                ->modalDescription('This will start a background queue worker to process jobs from the postnummer-updates queue.')
                ->modalSubmitActionLabel('Start Worker')
                ->action(function () {
                    try {
                        // Check if worker is already running
                        $output = [];
                        exec('ps aux | grep "queue:work" | grep -v grep', $output);

                        if (count($output) > 0) {
                            Notification::make()
                                ->warning()
                                ->title('Queue Worker Already Running')
                                ->body('A queue worker is already active.')
                                ->send();

                            return;
                        }

                        // Start queue worker in background
                        $command = 'cd ' . base_path() . ' && nohup php artisan queue:work --queue=postnummer-updates --tries=3 --timeout=300 > /dev/null 2>&1 & echo $!';
                        $pid = shell_exec($command);

                        if ($pid) {
                            Notification::make()
                                ->success()
                                ->title('Queue Worker Started')
                                ->body("Queue worker started with PID: {$pid}")
                                ->send();
                        } else {
                            throw new Exception('Failed to start queue worker');
                        }
                    } catch (Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Failed to Start Worker')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('stopWorker')
                ->label('Stop Queue Worker')
                ->icon('heroicon-o-stop')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Stop Queue Worker')
                ->modalDescription('This will stop all running queue workers. Jobs in progress will be interrupted.')
                ->modalSubmitActionLabel('Stop Worker')
                ->action(function () {
                    try {
                        // Find and kill queue worker processes
                        $output = [];
                        exec('ps aux | grep "queue:work" | grep -v grep | awk \'{print $2}\'', $output);

                        if (empty($output)) {
                            Notification::make()
                                ->warning()
                                ->title('No Queue Workers Running')
                                ->body('There are no active queue workers to stop.')
                                ->send();

                            return;
                        }

                        foreach ($output as $pid) {
                            exec("kill {$pid}");
                        }

                        Notification::make()
                            ->success()
                            ->title('Queue Worker Stopped')
                            ->body('All queue workers have been stopped.')
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Failed to Stop Worker')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('clearFailedJobs')
                ->label('Clear Failed Jobs')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Clear Failed Jobs')
                ->modalDescription('This will delete all failed jobs from the database. This action cannot be undone.')
                ->modalSubmitActionLabel('Clear Failed Jobs')
                ->action(function () {
                    try {
                        $count = DB::table('failed_jobs')->count();
                        DB::table('failed_jobs')->truncate();

                        Notification::make()
                            ->success()
                            ->title('Failed Jobs Cleared')
                            ->body("Cleared {$count} failed job(s).")
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Failed to Clear Jobs')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QueueMonitorWidget::class,
        ];
    }
}
