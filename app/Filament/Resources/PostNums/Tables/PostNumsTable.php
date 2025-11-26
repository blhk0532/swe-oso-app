<?php

namespace App\Filament\Resources\PostNums\Tables;

use App\Jobs\RunHittaSearchPersonsJob;
use App\Jobs\RunPostNumChecksJob;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class PostNumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('post_nummer')
                    ->label('Post Nr')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('post_ort')
                    ->label('Post Ort')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('post_lan')
                    ->label('Post Län')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('hitta_personer_total')
                    ->label('Hit P')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('hitta_foretag_total')
                    ->label('Hit F')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('ratsit_personer_total')
                    ->label('Rat P')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('ratsit_foretag_total')
                    ->label('Rat F')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('merinfo_personer_total')
                    ->label('Mer P')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('merinfo_foretag_total')
                    ->label('Mer F')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                //    ViewAction::make(),

                Action::make('run')
                    ->label('Run')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Ratsit/Hitta Scraper')
                    ->modalDescription(fn ($record) => "This will queue the post_ort_update.mjs script for post nummer: {$record->post_nummer}. The job will run in the background.")
                    ->modalSubmitActionLabel('Queue Job')
                    ->action(function ($record) {
                        // Set status to running
                        $record->update(['status' => 'running']);

                        // Create job with name and dispatch to queue
                        $job = new RunPostNumChecksJob($record->id);
                        dispatch($job);

                        // Update job name in database after dispatching
                        DB::table('jobs')
                            ->where('queue', 'postnummer-checks')
                            ->orderBy('id', 'desc')
                            ->limit(1)
                            ->update(['name' => 'Postnummer: ' . $record->post_nummer]);

                        Notification::make()
                            ->title('Kontroller har startats')
                            ->body("Postnummer {$record->post_nummer} kontroller har lagts i kön och körs i bakgrunden.")
                            ->info()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'running' && $record->status !== 'complete' && $record->status !== 'empty'),

                Action::make('runHittaSearch')
                    ->label('Hitta')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Hitta.se Person Search')
                    ->modalDescription(fn ($record) => "This will queue the hittaSearchPersons.mjs script for post nummer: {$record->post_nummer}. This will scrape person data from Hitta.se and may take several minutes.")
                    ->modalSubmitActionLabel('Queue Search')
                    ->action(function ($record) {
                        // Set status to running
                        $record->update(['status' => 'running']);

                        // Create job and dispatch to queue
                        $job = new RunHittaSearchPersonsJob($record->id, false); // false = no ratsit
                        dispatch($job)->onQueue('hitta-search');

                        Notification::make()
                            ->title('Hitta.se sökning har startats')
                            ->body("Postnummer {$record->post_nummer} person sökning har lagts i kön och körs i bakgrunden.")
                            ->info()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'running'),

                Action::make('runHittaSearchWithRatsit')
                    ->label('H+R')
                    ->icon('heroicon-o-users')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Hitta.se + Ratsit Person Search')
                    ->modalDescription(fn ($record) => "This will queue the hittaSearchPersons.mjs script with Ratsit integration for post nummer: {$record->post_nummer}. This will scrape person data from both Hitta.se and Ratsit.se and may take considerable time.")
                    ->modalSubmitActionLabel('Queue Search')
                    ->action(function ($record) {
                        // Set status to running
                        $record->update(['status' => 'running']);

                        // Create job and dispatch to queue
                        $job = new RunHittaSearchPersonsJob($record->id, true); // true = include ratsit
                        dispatch($job)->onQueue('hitta-search');

                        Notification::make()
                            ->title('Hitta.se + Ratsit sökning har startats')
                            ->body("Postnummer {$record->post_nummer} kombinerad sökning har lagts i kön och körs i bakgrunden.")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'running'),

                EditAction::make(),
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
                ]);
    }
}
