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
                    ->label('Run Hitta Search')
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
                    ->label('Run Hitta + Ratsit')
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
            ->groupedBulkActions([
                BulkActionGroup::make([
                    BulkAction::make('runChecks')
                        ->label('Run Checks')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Multiple Postnummer Checks')
                        ->modalDescription('This will queue checks for all selected postnummers as a batch job. They will run in the background.')
                        ->modalSubmitActionLabel('Queue Batch')
                        ->action(function (Collection $records) {
                            // Set status to running for all selected records
                            $records->each(function ($record) {
                                $record->update(['status' => 'running']);
                            });

                            // Create jobs for the batch
                            $jobs = $records->map(function ($record) {
                                return new RunPostNumChecksJob($record->id);
                            });

                            // Dispatch batch
                            $batch = Bus::batch($jobs)
                                ->name('Postnummer Batch: ' . now()->format('Y-m-d H:i:s'))
                                ->onQueue('postnummer-checks')
                                ->dispatch();

                            Notification::make()
                                ->title('Batch job har startats')
                                ->body("{$records->count()} postnummer kontroller har lagts i kön som batch {$batch->id} och körs i bakgrunden.")
                                ->info()
                                ->send();

                            // Return true to indicate success and keep records selected
                            return true;
                        })
                        ->visible(function (Collection $records) {
                            // Show the action if we have records selected
                            return $records->isNotEmpty();
                        }),

                    BulkAction::make('runHittaSearch')
                        ->label('Run Hitta Search')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Hitta.se Person Search for Multiple Postnummers')
                        ->modalDescription('This will queue hittaSearchPersons.mjs for all selected postnummers. Each search will scrape person data from Hitta.se and may take several minutes per postal code.')
                        ->modalSubmitActionLabel('Queue Searches')
                        ->action(function (Collection $records) {
                            // Set status to running for all selected records
                            $records->each(function ($record) {
                                $record->update(['status' => 'running']);
                            });

                            // Create jobs for the batch
                            $jobs = $records->map(function ($record) {
                                return new RunHittaSearchPersonsJob($record->id, false);
                            });

                            // Dispatch batch
                            $batch = Bus::batch($jobs)
                                ->name('Hitta Search Batch: ' . now()->format('Y-m-d H:i:s'))
                                ->onQueue('hitta-search')
                                ->dispatch();

                            Notification::make()
                                ->title('Hitta.se sökningar har startats')
                                ->body("{$records->count()} postnummer sökningar har lagts i kön som batch {$batch->id} och körs i bakgrunden.")
                                ->info()
                                ->send();

                            return true;
                        })
                        ->visible(function (Collection $records) {
                            return $records->isNotEmpty();
                        }),

                    BulkAction::make('runHittaSearchWithRatsit')
                        ->label('Run Hitta + Ratsit')
                        ->icon('heroicon-o-users')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Hitta.se + Ratsit Person Search for Multiple Postnummers')
                        ->modalDescription('This will queue hittaSearchPersons.mjs with Ratsit integration for all selected postnummers. Each search will scrape person data from both services and may take considerable time.')
                        ->modalSubmitActionLabel('Queue Searches')
                        ->action(function (Collection $records) {
                            // Set status to running for all selected records
                            $records->each(function ($record) {
                                $record->update(['status' => 'running']);
                            });

                            // Create jobs for the batch
                            $jobs = $records->map(function ($record) {
                                return new RunHittaSearchPersonsJob($record->id, true);
                            });

                            // Dispatch batch
                            $batch = Bus::batch($jobs)
                                ->name('Hitta+Ratsit Search Batch: ' . now()->format('Y-m-d H:i:s'))
                                ->onQueue('hitta-search')
                                ->dispatch();

                            Notification::make()
                                ->title('Hitta.se + Ratsit sökningar har startats')
                                ->body("{$records->count()} kombinerade sökningar har lagts i kön som batch {$batch->id} och körs i bakgrunden.")
                                ->warning()
                                ->send();

                            return true;
                        })
                        ->visible(function (Collection $records) {
                            return $records->isNotEmpty();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
