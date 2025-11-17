<?php

namespace App\Filament\Resources\HittaForetagQueues\Tables;

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

class HittaForetagQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')->label('Post Nr')->searchable()->sortable(),
                TextColumn::make('post_ort')->label('Post Ort')->searchable()->sortable(),
                TextColumn::make('post_lan')->label('Post Län')->searchable()->sortable(),
                TextColumn::make('foretag_total')->label('F T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('foretag_saved')->label('F S')->numeric()->sortable()->alignCenter(),
                TextColumn::make('foretag_phone')->label('F Phone')->numeric()->sortable()->alignCenter(),
                TextColumn::make('foretag_page')->label('Page')->numeric()->sortable()->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('foretag_pages')->label('Pages')->numeric()->sortable()->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('foretag_status')->label('Status')->badge()->color(fn ($state) => $state === 'complete' ? 'success' : ($state === 'running' ? 'info' : 'gray')),
                IconColumn::make('foretag_queued')->label('F Q')->boolean(),
                IconColumn::make('foretag_scraped')->label('F X')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active Status'),
                TernaryFilter::make('foretag_queued')->label('Companies Queued'),
                TernaryFilter::make('foretag_scraped')->label('Companies Scraped'),
                SelectFilter::make('foretag_status')->label('Status')->options([
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
                    BulkAction::make('queueForetag')
                        ->label('Queue Foretag')
                        ->icon('heroicon-o-queue-list')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Companies')
                        ->modalDescription('Queue selected records for foretag scraping?')
                        ->modalSubmitActionLabel('Yes, Queue')
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['foretag_queued' => true]));
                            Notification::make()->title('Queued')->body(count($records) . ' företags queued.')->success()->send();
                        }),
                    BulkAction::make('unqueueForetag')
                        ->label('Unqueue Foretag')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Unqueue Companies')
                        ->modalDescription('Remove foretag queue flag for selected records?')
                        ->modalSubmitActionLabel('Yes, Unqueue')
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['foretag_queued' => false]));
                            Notification::make()->title('Unqueued')->body(count($records) . ' foretag unqueued.')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
