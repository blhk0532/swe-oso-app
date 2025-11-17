<?php

namespace App\Filament\Resources\HittaQueues\Tables;

use App\Jobs\RunHittaCountsForQueue;
use App\Jobs\RunHittaForQueue;
use App\Jobs\RunHittaRatsitForQueue;
use App\Support\QueueAutostart;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class HittaQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')->label('Post Nr')->searchable()->sortable(),
                TextColumn::make('post_ort')->label('Post Ort')->searchable()->sortable(),
                TextColumn::make('post_lan')->label('Post Län')->searchable()->sortable(),
                TextColumn::make('foretag_total')->label('F T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('personer_total')->label('P T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('foretag_saved')->label('F S')->numeric()->sortable()->alignCenter(),
                TextColumn::make('personer_saved')->label('P S')->numeric()->sortable()->alignCenter(),
                IconColumn::make('foretag_queued')->label('F Q')->boolean(),
                IconColumn::make('personer_queued')->label('P Q')->boolean(),
                IconColumn::make('foretag_scraped')->label('F X')->boolean(),
                IconColumn::make('personer_scraped')->label('P X')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active Status'),
                TernaryFilter::make('personer_queued')->label('Persons Queued'),
                TernaryFilter::make('personer_scraped')->label('Persons Scraped'),
            ])
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions())
            ->defaultSort('created_at', 'desc');
    }

    protected static function recordActions(): array
    {
        return [
            Action::make('checkCount')
                ->label('Check Count')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Check Hitta Count')
                ->modalDescription('This will run the hittaCounts.mjs script to fetch person and company counts for this post nummer.')
                ->modalSubmitActionLabel('Run Count Check')
                ->action(function ($record) {
                    RunHittaCountsForQueue::dispatch($record);

                    Notification::make()
                        ->title('Count Check Queued')
                        ->body("Count extraction queued for post nummer: {$record->post_nummer}")
                        ->success()
                        ->send();
                })
                ->visible(fn ($record) => $record->personer_status !== 'running'),

            Action::make('runHitta')
                ->label('Run Hitta')
                ->icon('heroicon-o-magnifying-glass')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Run Hitta Script')
                ->modalDescription('This will run the hitta.mjs script to scrape person and company data for this post nummer.')
                ->modalSubmitActionLabel('Run Hitta Scraper')
                ->action(function ($record) {
                    RunHittaForQueue::dispatch($record);

                    Notification::make()
                        ->title('Hitta Scraper Queued')
                        ->body("Hitta scraping queued for post nummer: {$record->post_nummer}")
                        ->success()
                        ->send();
                })
                ->visible(fn ($record) => $record->personer_status !== 'running'),

            Action::make('runHittaRatsit')
                ->label('Run Hitta+Ratsit')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Run Hitta+Ratsit Script')
                ->modalDescription('This will run the hitta_ratsit.mjs script to scrape person and company data from both hitta.se and ratsit.se for this post nummer.')
                ->modalSubmitActionLabel('Run Combined Scraper')
                ->action(function ($record) {
                    RunHittaRatsitForQueue::dispatch($record);

                    Notification::make()
                        ->title('Hitta+Ratsit Scraper Queued')
                        ->body("Combined scraping queued for post nummer: {$record->post_nummer}")
                        ->success()
                        ->send();

                    QueueAutostart::attempt('filament');
                })
                ->visible(fn ($record) => $record->personer_status !== 'running'),

            ViewAction::make(),
            EditAction::make(),
        ];
    }

    protected static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('bulkCheckCount')
                    ->label('Bulk Check Count')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Bulk Check Hitta Count')
                    ->modalDescription('Run hittaCounts.mjs script for all selected records to fetch person and company counts.')
                    ->modalSubmitActionLabel('Run Count Checks')
                    ->action(function (Collection $records) {
                        $queued = 0;
                        foreach ($records as $record) {
                            if ($record->personer_status === 'running') {
                                continue;
                            }
                            RunHittaCountsForQueue::dispatch($record);
                            $queued++;
                        }

                        Notification::make()
                            ->title('Count Checks Queued')
                            ->body("Queued {$queued} count extraction job(s)")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('bulkRunHitta')
                    ->label('Bulk Run Hitta')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Bulk Run Hitta Script')
                    ->modalDescription('Run hitta.mjs script for all selected records to scrape person and company data.')
                    ->modalSubmitActionLabel('Run Hitta Scrapers')
                    ->action(function (Collection $records) {
                        $queued = 0;
                        foreach ($records as $record) {
                            if ($record->personer_status === 'running') {
                                continue;
                            }
                            RunHittaForQueue::dispatch($record);
                            $queued++;
                        }

                        Notification::make()
                            ->title('Hitta Scrapers Queued')
                            ->body("Queued {$queued} hitta scraping job(s)")
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
                    ->modalDescription('Run hitta_ratsit.mjs script for all selected records to scrape person and company data from both hitta.se and ratsit.se.')
                    ->modalSubmitActionLabel('Run Combined Scrapers')
                    ->action(function (Collection $records) {
                        $queued = 0;
                        foreach ($records as $record) {
                            if ($record->personer_status === 'running') {
                                continue;
                            }
                            RunHittaRatsitForQueue::dispatch($record);
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

                BulkAction::make('queueForetag')
                    ->label('Queue Företag')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Företag')
                    ->modalDescription('Queue selected records for företag scraping?')
                    ->modalSubmitActionLabel('Yes, Queue')
                    ->action(function (Collection $records) {
                        $records->each(fn ($record) => $record->update(['foretag_queued' => true]));
                        Notification::make()->title('Queued')->body(count($records) . ' företag queued.')->success()->send();
                    }),
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
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
