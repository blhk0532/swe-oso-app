<?php

namespace App\Filament\Resources\MerinfoQueues\Tables;

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

class MerinfoQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')
                    ->label('Post Nr')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post_ort')
                    ->label('Post Ort')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post_lan')
                    ->label('Post Län')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('foretag_total')
                    ->label('F T')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('personer_total')
                    ->label('P T')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('foretag_saved')
                    ->label('F S')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('personer_saved')
                    ->label('P S')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('foretag_queued')
                    ->label('F Q')
                    ->boolean(),
                IconColumn::make('personer_queued')
                    ->label('P Q')
                    ->boolean(),
                IconColumn::make('foretag_scraped')
                    ->label('F X')
                    ->boolean(),
                IconColumn::make('personer_scraped')
                    ->label('P X')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
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
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                TernaryFilter::make('personer_queued')
                    ->label('Persons Queued'),
                TernaryFilter::make('personer_scraped')
                    ->label('Persons Scraped'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('queueForetag')
                        ->label('Queue for Företag Scraping')
                        ->icon('heroicon-o-building-office')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Queue for Företag Scraping')
                        ->modalDescription('Are you sure you want to queue these records for företag scraping?')
                        ->modalSubmitActionLabel('Yes, Queue Them')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['foretag_queued' => true]);
                            });

                            Notification::make()
                                ->title('Records queued successfully')
                                ->body(count($records) . ' records have been queued for företag scraping.')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('queuePersoner')
                        ->label('Queue for Personer Scraping')
                        ->icon('heroicon-o-queue-list')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Queue for Personer Scraping')
                        ->modalDescription('Are you sure you want to queue these records for personer scraping?')
                        ->modalSubmitActionLabel('Yes, Queue Them')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['personer_queued' => true]);
                            });

                            Notification::make()
                                ->title('Records queued successfully')
                                ->body(count($records) . ' records have been queued for personer scraping.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
