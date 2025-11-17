<?php

namespace App\Filament\Resources\HittaPersonerQueues\Tables;

use App\Jobs\RunHittaCountsForPersonerQueue;
use App\Jobs\RunHittaPersonsDataForQueue;
use App\Jobs\RunHittaRatsitForPersonerQueue;
use App\Support\QueueAutostart;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class HittaPersonerQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')->label('Post Nr')->searchable()->sortable(),
                TextColumn::make('post_ort')->label('Post Ort')->searchable()->sortable(),
                TextColumn::make('post_lan')->label('Post Län')->searchable()->sortable(),
                TextColumn::make('personer_total')->label('P T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('personer_saved')->label('P S')->numeric()->sortable()->alignCenter(),
                TextColumn::make('personer_phone')->label('P Phone')->numeric()->sortable()->alignCenter(),
                TextColumn::make('personer_house')->label('P House')->numeric()->sortable()->alignCenter(),
                TextColumn::make('personer_page')->label('Page')->numeric()->sortable()->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personer_pages')->label('Pages')->numeric()->sortable()->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personer_status')->label('Status')->badge()->color(fn ($state) => $state === 'complete' ? 'success' : ($state === 'running' ? 'info' : 'gray')),
                IconColumn::make('personer_queued')->label('P Q')->boolean(),
                IconColumn::make('personer_scraped')->label('P X')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active Status'),
                TernaryFilter::make('personer_queued')->label('Persons Queued'),
                TernaryFilter::make('personer_scraped')->label('Persons Scraped'),
                SelectFilter::make('personer_status')->label('Status')->options([
                    'pending' => 'Pending',
                    'running' => 'Running',
                    'complete' => 'Complete',
                    'failed' => 'Failed',
                ])->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkRunHittaCount')
                        ->label('Bulk Run Hitta Count')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Run Hitta Count')
                        ->modalDescription('Run hittaCounts.mjs script for all selected records to fetch person counts.')
                        ->modalSubmitActionLabel('Run Counts')
                        ->action(function (Collection $records) {
                            $queued = 0;
                            foreach ($records as $record) {
                                if ($record->personer_status === 'running') {
                                    continue;
                                }
                                RunHittaCountsForPersonerQueue::dispatch($record);
                                $queued++;
                            }

                            Notification::make()
                                ->title('Hitta Counts Queued')
                                ->body("Queued {$queued} count extraction job(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkRunHittaPersonsData')
                        ->label('Bulk Run Persons Data')
                        ->icon('heroicon-o-users')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Run Persons Data')
                        ->modalDescription('Run hittaSearchPersons.mjs script for all selected records to scrape person data.')
                        ->modalSubmitActionLabel('Run Scrapers')
                        ->action(function (Collection $records) {
                            $queued = 0;
                            foreach ($records as $record) {
                                if ($record->personer_status === 'running') {
                                    continue;
                                }
                                RunHittaPersonsDataForQueue::dispatch($record);
                                $queued++;
                            }

                            Notification::make()
                                ->title('Persons Data Queued')
                                ->body("Queued {$queued} person data scraping job(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkRunHittaRatsit')
                        ->label('Bulk Run Hitta+Ratsit')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Run Hitta+Ratsit Script')
                        ->modalDescription('Run hitta_ratsit.mjs script for all selected records to scrape person data from both hitta.se and ratsit.se.')
                        ->modalSubmitActionLabel('Run Combined Scrapers')
                        ->action(function (Collection $records) {
                            $queued = 0;
                            foreach ($records as $record) {
                                if ($record->personer_status === 'running') {
                                    continue;
                                }
                                RunHittaRatsitForPersonerQueue::dispatch($record);
                                $queued++;
                            }

                            Notification::make()
                                ->title('Hitta+Ratsit Scrapers Queued')
                                ->body("Queued {$queued} combined scraping job(s)")
                                ->success()
                                ->send();

                            QueueAutostart::attempt('filament');
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('queuePersoner')
                        ->label('Queue Personer')
                        ->icon('heroicon-o-queue-list')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Personer')
                        ->modalDescription('Queue selected records for personer scraping?')
                        ->modalSubmitActionLabel('Yes, Queue')
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['personer_queued' => true]));
                            Notification::make()->title('Queued')->body(count($records) . ' personer queued.')->success()->send();
                        }),
                    BulkAction::make('unqueuePersoner')
                        ->label('Unqueue Personer')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Unqueue Personer')
                        ->modalDescription('Remove personer queue flag for selected records?')
                        ->modalSubmitActionLabel('Yes, Unqueue')
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['personer_queued' => false]));
                            Notification::make()->title('Unqueued')->body(count($records) . ' personer unqueued.')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
