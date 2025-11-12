<?php

namespace App\Filament\Resources\RatsitQues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RatsitQuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('personnamn')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),

                TextColumn::make('personnummer')
                    ->label('Personnummer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('gatuadress')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('postnummer')
                    ->label('Postnummer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('postort')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kommun')
                    ->label('Municipality')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('lan')
                    ->label('County')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('telefon')
                    ->label('Phone')
                    ->formatStateUsing(fn ($state) => is_array($state) && count($state) > 0 ? implode(', ', array_slice($state, 0, 2)) : '-')
                    ->toggleable(),

                TextColumn::make('alder')
                    ->label('Age')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('kon')
                    ->label('Sex')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('bostadstyp')
                    ->label('Housing Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('kommun')
                    ->label('Municipality')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('lan')
                    ->label('County')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('kon')
                    ->label('Sex')
                    ->options([
                        'Man' => 'Man',
                        'Kvinna' => 'Kvinna',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
