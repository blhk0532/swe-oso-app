<?php

namespace App\Filament\Resources\PostNummers\Tables;

use App\Jobs\CheckHittaTotals;
use App\Jobs\ProcessPostNummer;
use App\Jobs\RunHittaCountForPostNummer;
use App\Jobs\RunHittaRatsitScript;
use App\Jobs\RunHittaScript;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Log;
use Symfony\Component\Process\Process;

class PostNummersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('post_nummer')
                    ->label('Post Nr')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap']),

                TextColumn::make('post_ort')
                    ->label('Post Ort')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'whitespace-nowrap'])
                    ->placeholder('—'),

                //    TextColumn::make('post_lan')
                //        ->label('Post Län')
                //        ->searchable()
                //        ->sortable()
                //        ->extraAttributes(['class' => 'whitespace-nowrap'])
                //        ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('TE')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('house')
                    ->label('HS')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                //     TextColumn::make('platser')
                //                   ->label('PL')
                //                   ->numeric()
                //                   ->sortable()
                //                   ->grow(false)
                //                   ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                //                   ->placeholder('—'),
                //

                TextColumn::make('count')
                    ->label('CN')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('total_count')
                    ->label('TT')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('bolag')
                    ->label('AB')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('foretag')
                    ->label('FÖ')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('personer')
                    ->label('PE')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('merinfo_personer')
                    ->label('MI PE')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('merinfo_foretag')
                    ->label('MI FÖ')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('platser')
                    ->label('PL')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('computed_progress')
                    ->label('%')
                    ->html()
                    ->getStateUsing(function ($record): string {
                        $total = (int) ($record->total_count ?? 0);
                        $done = (int) ($record->count ?? 0);
                        $p = $total > 0 ? (int) min(100, floor(($done / $total) * 100)) : (int) ($record->progress ?? 0);

                        return "<div class='w-28'>"
                            . "<div class='h-1.5 w-full bg-gray-200/60 dark:bg-gray-700/50 rounded'>"
                            . "<div class='h-1.5 bg-blue-500 rounded' style='width: {$p}%;'></div>"
                            . '</div>'
                            . "<div class='mt-1 text-xs text-gray-500 dark:text-gray-400'>{$p}%</div>"
                            . '</div>';
                    })
                    ->alignCenter()
                    ->sortable()
                    ->grow(false),

                IconColumn::make('is_active')
                    ->label('OK')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'gray',
                        'running' => 'warning',
                        'complete' => 'success',
                        'empty' => 'danger',
                        'queued_hitta' => 'info',
                        'running_hitta' => 'warning',
                        'failed_hitta' => 'danger',
                        'error_hitta' => 'danger',
                        'Checked' => 'success',
                        'queued_hitta_ratsit' => 'info',
                        'running_hitta_ratsit' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst(str_replace('_', ' ', $state)) : '—')
                    ->sortable()
                    ->grow(false),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'complete' => 'Complete',
                        'empty' => 'Empty',
                        'queued_hitta' => 'Queued Hitta',
                        'running_hitta' => 'Running Hitta',
                        'failed_hitta' => 'Failed Hitta',
                        'error_hitta' => 'Error Hitta',
                        'Checked' => 'Checked',
                        'queued_hitta_ratsit' => 'Queued Hitta+Ratsit',
                        'running_hitta_ratsit' => 'Running Hitta+Ratsit',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                Action::make('run')
                    ->label('Run')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Ratsit/Hitta Scraper')
                    ->modalDescription(fn ($record) => "This will queue the post_ort_update.mjs script for post nummer: {$record->post_nummer}. The job will run in the background.")
                    ->modalSubmitActionLabel('Queue Job')
                    ->action(function ($record) {
                        // Dispatch the job to the queue
                        ProcessPostNummer::dispatch($record->post_nummer);

                        // Optimistically reflect in UI immediately
                        $record->update([
                            'status' => 'running',
                            'is_active' => true,
                        ]);

                        Notification::make()
                            ->title('Job Queued')
                            ->body("Update queued for post nummer: {$record->post_nummer}. Start a queue worker to begin processing.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'running' && $record->status !== 'complete' && $record->status !== 'empty'),

                Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Pause Processing')
                    ->modalDescription(fn ($record) => "This will stop processing post nummer: {$record->post_nummer}. Progress will be saved and can be resumed later.")
                    ->modalSubmitActionLabel('Pause Job')
                    ->action(function ($record) {
                        // Update status to pending (will be picked up for resume)
                        $record->update([
                            'status' => 'pending',
                            'is_active' => false,
                        ]);

                        Notification::make()
                            ->title('Job Paused')
                            ->body("Processing paused for post nummer: {$record->post_nummer}. Progress has been saved.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'running'),

                Action::make('update')
                    ->label('Run')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Re-run Complete Post Nummer')
                    ->modalDescription(fn ($record) => "This will re-run the scraper for post nummer: {$record->post_nummer}. The job will run in the background.")
                    ->modalSubmitActionLabel('Queue Job')
                    ->action(function ($record) {
                        // Reset progress for fresh run
                        $record->update([
                            'status' => 'running',
                            'is_active' => true,
                            'progress' => 0,
                            'count' => 0,
                            'phone' => 0,
                            'house' => 0,
                            'bolag' => 0,
                            'last_processed_page' => 0,
                            'processed_count' => 0,
                        ]);

                        // Dispatch the job to the queue
                        ProcessPostNummer::dispatch($record->post_nummer);

                        Notification::make()
                            ->title('Job Queued')
                            ->body("Update queued for post nummer: {$record->post_nummer}. Start a queue worker to begin processing.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'complete'),

                Action::make('delete')
                    ->label('xXx')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Post Nummer')
                    ->modalDescription('This will permanently delete this post nummer record. This action cannot be undone.')
                    ->modalSubmitActionLabel('Delete')
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->title('Post Nummer Deleted')
                            ->body("Post nummer {$record->post_nummer} has been deleted.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'empty'),

                EditAction::make(),

                Action::make('checkTotals')
                    ->label('Scan')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Check Hitta Totals')
                    ->modalDescription(fn ($record) => "This will run a light check to fetch only total results for post nummer: {$record->post_nummer} without processing all pages.")
                    ->modalSubmitActionLabel('Run Check')
                    ->action(function ($record) {
                        CheckHittaTotals::dispatch($record->post_nummer);

                        // Auto-start queue worker
                        $workerProcess = new Process([
                            'php',
                            base_path('artisan'),
                            'queue:work',
                            'database',
                            '--queue=postnummer',
                            '--tries=3',
                            '--timeout=0',
                        ]);
                        $workerProcess->start();

                        Notification::make()
                            ->title('Totals Check Queued & Worker Started')
                            ->body("Totals check queued for post nummer: {$record->post_nummer}. Queue worker started automatically.")
                            ->success()
                            ->send();
                    }),

                Action::make('checkCounts')
                    ->label('Check')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Check Hitta Counts')
                    ->modalDescription(fn ($record) => "This will run the hittaCount script to fetch Företag/Personer/Platser counts for post nummer: {$record->post_nummer} and update totals.")
                    ->modalSubmitActionLabel('Run')
                    ->action(function ($record) {
                        RunHittaCountForPostNummer::dispatch($record);

                        // Start a queue worker in the background (stops when empty)
                        $command = 'php ' . base_path('artisan') . ' queue:work database --queue=postnummer --tries=3 --timeout=0 --stop-when-empty > /dev/null 2>&1 &';
                        shell_exec($command);

                        Notification::make()
                            ->title('Hitta Counts Queued')
                            ->body("Counts check queued for post nummer: {$record->post_nummer}. A background worker has been started and will stop when all jobs are done.")
                            ->success()
                            ->send();
                    }),

            ])
            ->toolbarActions([

                BulkActionGroup::make([
                    BulkAction::make('bulkResetValues')
                        ->label('Bulk Reset Values')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Reset Selected Post Nummer Values')
                        ->modalDescription('This will stop all queue workers, clear all pending jobs, and reset status, is_active, progress, count, total_count, phone, house, bolag, foretag, personer, platser, last_processed_page, processed_count, is_pending, is_complete for all selected post nummers.')
                        ->action(function (Collection $records): void {
                            // First, stop all queue workers
                            $workerProcess = new Process(['pkill', '-f', 'artisan queue:work database']);
                            $workerProcess->run();

                            // Clear all pending jobs from the queue
                            $clearProcess = new Process(['php', base_path('artisan'), 'queue:clear', 'database', '--queue=postnummer']);
                            $clearProcess->run();

                            // Reset all selected records
                            $reset = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => null,
                                    'is_active' => false,
                                    'progress' => 0,
                                    'count' => 0,
                                    'total_count' => 0,
                                    'phone' => 0,
                                    'house' => 0,
                                    'bolag' => 0,
                                    'foretag' => 0,
                                    'personer' => 0,
                                    'platser' => 0,
                                    'last_processed_page' => 0,
                                    'processed_count' => 0,
                                    'is_pending' => false,
                                    'is_complete' => false,
                                ]);
                                $reset++;
                            }

                            Notification::make()
                                ->title('Bulk Reset Complete')
                                ->body("Stopped queue workers, cleared pending jobs, and reset {$reset} post nummer row(s).")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),
                    BulkAction::make('queueRun')
                        ->label('Queue Run')
                        ->icon('heroicon-o-play')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Post Ort Run')
                        ->modalDescription('Queue post_ort_update.mjs scraper for all selected post nummers. Already running records will be skipped.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'running') {
                                    continue;
                                }

                                ProcessPostNummer::dispatch($record->post_nummer);
                                $queued++;

                                // Optimistic UI update so table reflects change immediately
                                $record->update([
                                    'status' => 'running',
                                    'is_active' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Jobs Queued')
                                ->body("Queued {$queued} job(s). Start a queue worker to begin processing.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),

                    BulkAction::make('runHitta')
                        ->label('Run Hitta')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Run Hitta Script')
                        ->modalDescription('Run hitta.mjs scraper for all selected post nummers. This will scrape person data from hitta.se and save to database.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'running_hitta') {
                                    continue;
                                }

                                RunHittaScript::dispatch($record->post_nummer);
                                $queued++;

                                // Optimistic UI update
                                $record->update([
                                    'status' => 'queued_hitta',
                                    'is_active' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Hitta Jobs Queued')
                                ->body("Queued {$queued} hitta job(s). Start a queue worker to begin processing.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),

                    BulkAction::make('runHittaRatsit')
                        ->label('Run Hitta+Ratsit')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Run Hitta+Ratsit Script')
                        ->modalDescription('Run hitta_ratsit.mjs combined scraper for all selected post nummers. This will scrape from both hitta.se and ratsit.se.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'running_hitta_ratsit') {
                                    continue;
                                }

                                RunHittaRatsitScript::dispatch($record->post_nummer);
                                $queued++;

                                // Optimistic UI update
                                $record->update([
                                    'status' => 'queued_hitta_ratsit',
                                    'is_active' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Hitta+Ratsit Jobs Queued')
                                ->body("Queued {$queued} hitta+ratsit job(s). Start a queue worker to begin processing.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),

                    BulkAction::make('addToQueue')
                        ->label('Add to Queue')
                        ->icon('heroicon-o-queue-list')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Add to Post Ort Queue')
                        ->modalDescription('Add selected post nummers to the post_ort_update.mjs processing queue.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'running') {
                                    continue;
                                }

                                ProcessPostNummer::dispatch($record->post_nummer);
                                $queued++;

                                // Optimistic UI update
                                $record->update([
                                    'status' => 'running',
                                    'is_active' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Added to Queue')
                                ->body("Added {$queued} post nummer(s) to processing queue.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),

                    DeleteBulkAction::make(),

                    BulkAction::make('bulkCheckCounts')
                        ->label('Check Counts')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Check Hitta Counts')
                        ->modalDescription('Run hittaCounts for selected rows to update Företag, Personer, Platser. Rows will be queued and processed by a background worker.')
                        ->action(function (Collection $records): void {
                            $queued = 0;
                            foreach ($records as $record) {
                                RunHittaCountForPostNummer::dispatch($record);
                                $queued++;
                            }

                            // Start a queue worker in the background (stops when empty)
                            $command = 'php ' . base_path('artisan') . ' queue:work database --queue=postnummer --tries=3 --timeout=0 --stop-when-empty > /dev/null 2>&1 &';
                            shell_exec($command);

                            Notification::make()
                                ->title('Counts Check Queued')
                                ->body("Counts check queued for {$queued} row(s). A background worker has been started and will stop when all jobs are done.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),

                    BulkAction::make('bulkCheckTotals')
                        ->label('Check Totals')
                        ->icon('heroicon-o-magnifying-glass')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Check Hitta Totals')
                        ->modalDescription('Run a light totals check for selected rows that are not already empty (status and total_count both null). Empty rows will be skipped.')
                        ->action(function (Collection $records): void {
                            $queued = 0;
                            $skipped = 0;
                            foreach ($records as $record) {
                                // Skip rows already empty (status = 'empty')
                                if ($record->status === 'empty') {
                                    $skipped++;

                                    continue;
                                }
                                CheckHittaTotals::dispatch($record->post_nummer);
                                $queued++;
                            }

                            Notification::make()
                                ->title('Totals Check Queued')
                                ->body("Queued {$queued} check(s). Skipped {$skipped} empty row(s). Start a queue worker to begin processing.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),
                ]),
                Action::make('startQueueWorkers')
                    ->label('Start Queue')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Start Multiple Queue Workers')
                    ->modalDescription('This will start 1 queue worker in the background to process jobs sequentially. Worker will automatically stop when all jobs are completed.')
                    ->modalSubmitActionLabel('Start Worker')
                    ->action(function () {
                        // Start queue worker that stops when empty using shell_exec for web context
                        $command = 'php ' . base_path('artisan') . ' queue:work database --queue=postnummer --tries=3 --timeout=0 --stop-when-empty > /dev/null 2>&1 &';
                        shell_exec($command);

                        Notification::make()
                            ->title('Queue Worker Started')
                            ->body('Queue worker started in the background. It will automatically stop when all jobs are completed.')
                            ->success()
                            ->send();
                    }),

                Action::make('statusQueueWorkers')
                    ->label('Status Queue')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(function () {
                        // Check if queue workers are running
                        $checkProcess = new Process(['pgrep', '-f', 'artisan queue:work database']);
                        $checkProcess->run();

                        if ($checkProcess->isSuccessful()) {
                            $output = trim($checkProcess->getOutput());
                            $pids = explode("\n", $output);
                            $workerCount = count(array_filter($pids)); // Filter out empty lines

                            Notification::make()
                                ->title('Queue Workers Status')
                                ->body("{$workerCount} queue worker(s) are currently running and processing jobs.")
                                ->info()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Queue Workers Status')
                                ->body('No queue workers are currently running. Workers start automatically when jobs are queued and stop when all jobs are completed.')
                                ->info()
                                ->send();
                        }
                    }),

                Action::make('clearQueueWorkers')
                    ->label('Clear Queue')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Stop All Queue Workers')
                    ->modalDescription('This will stop all running queue workers. Any jobs currently being processed may be interrupted.')
                    ->modalSubmitActionLabel('Stop Workers')
                    ->action(function () {
                        $process = new Process(['pkill', '-f', 'artisan queue:work database']);

                        try {
                            $process->run();

                            // pkill returns 0 if processes were killed, 1 if no processes found
                            // Both are acceptable outcomes
                            if ($process->getExitCode() === 0) {
                                Notification::make()
                                    ->title('Queue Workers Stopped')
                                    ->body('All queue workers have been stopped successfully.')
                                    ->success()
                                    ->send();
                            } elseif ($process->getExitCode() === 1) {
                                Notification::make()
                                    ->title('No Workers Running')
                                    ->body('No queue workers were found running.')
                                    ->info()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Failed to Stop Workers')
                                    ->body('Could not stop queue workers. There was an error: ' . $process->getErrorOutput())
                                    ->warning()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            Log::error('Failed to stop queue workers: ' . $e->getMessage());

                            Notification::make()
                                ->title('Error Stopping Workers')
                                ->body('An error occurred while trying to stop queue workers.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort(function ($query) {
                $query->orderByRaw("CASE 
                    WHEN status = 'running' THEN 0 
                    WHEN status = 'pending' THEN 1 
                    ELSE 2 
                END")
                    ->orderBy('post_nummer', 'asc');
            })
            ->paginated([10, 25, 50, 100, 200, 500, 1000]);
    }
}
