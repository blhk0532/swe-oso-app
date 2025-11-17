<?php

namespace App\Filament\Resources\HittaPersonerDatas\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HittaDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('personnamn')->label('Name')->searchable(),
                TextColumn::make('gatuadress')->label('Address'),
                TextColumn::make('postnummer')->label('Post Nr'),
                TextColumn::make('postort')->label('City'),
                TextColumn::make('telefon')->label('Phone'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
