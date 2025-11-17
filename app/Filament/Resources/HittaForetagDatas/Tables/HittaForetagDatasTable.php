<?php

namespace App\Filament\Resources\HittaForetagDatas\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HittaForetagDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('foretagsnamn')->label('Company')->searchable()->sortable()->weight('bold'),
                TextColumn::make('orgnummer')->label('Org Number')->sortable(),
                TextColumn::make('postort')->label('City')->searchable()->sortable(),
                TextColumn::make('postnummer')->label('Zip')->sortable(),
                TextColumn::make('telefon')->label('Phone')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('is_telefon')->label('Has Phone')->boolean(),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_telefon')->label('Has phone'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
