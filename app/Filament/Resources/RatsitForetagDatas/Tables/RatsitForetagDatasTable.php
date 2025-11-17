<?php

namespace App\Filament\Resources\RatsitForetagDatas\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RatsitForetagDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('foretagsnamn')->label('Company')->searchable()->sortable()->weight('bold'),
                TextColumn::make('orgnummer')->label('Org')->searchable()->sortable(),
                TextColumn::make('postort')->label('City')->searchable()->sortable(),
                TextColumn::make('postnummer')->label('Zip')->sortable(),
                TextColumn::make('telefon')->label('Phone')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
