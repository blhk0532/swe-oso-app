<?php

namespace App\Filament\Resources\MerinfoPersonerQueues\Tables;

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

class MerinfoPersonerQueuesTable
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
