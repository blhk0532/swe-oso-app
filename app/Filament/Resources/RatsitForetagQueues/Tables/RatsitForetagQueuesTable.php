<?php

namespace App\Filament\Resources\RatsitForetagQueues\Tables;

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

class RatsitForetagQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')->label('Post Nr')->searchable()->sortable(),
                TextColumn::make('post_ort')->label('City')->searchable()->sortable(),
                TextColumn::make('post_lan')->label('Region')->searchable()->sortable(),
                TextColumn::make('foretag_total')->label('F T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('foretag_saved')->label('F S')->numeric()->sortable()->alignCenter(),
                TextColumn::make('foretag_phone')->label('F Phone')->numeric()->sortable()->alignCenter(),
                IconColumn::make('foretag_queued')->label('F Q')->boolean(),
                IconColumn::make('foretag_scraped')->label('F X')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['foretag_queued' => true]));
                            Notification::make()->title('Queued')->body(count($records) . ' foretag queued.')->success()->send();
                        }),
                    BulkAction::make('unqueueForetag')
                        ->label('Unqueue Foretag')
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
