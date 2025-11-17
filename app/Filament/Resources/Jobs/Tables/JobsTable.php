<?php

namespace App\Filament\Resources\Jobs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s') // Auto-refresh every 5 seconds
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('queue')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payload')
                    ->label('Job Data')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->tooltip(function ($record) {
                        return json_encode($record->payload, JSON_PRETTY_PRINT);
                    }),
                TextColumn::make('attempts')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved_at')
                    ->label('Reserved At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not reserved'),
                TextColumn::make('available_at')
                    ->label('Available At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
