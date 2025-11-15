<?php

namespace App\Filament\Resources\PostNummerPersonerQueues\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostNummerPersonerQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')->label('Post Nr')->searchable()->sortable(),
                TextColumn::make('post_ort')->label('Post Ort')->searchable()->sortable(),
                TextColumn::make('post_lan')->label('Post Län')->searchable()->sortable(),

                TextColumn::make('merinfo_personer_total')->label('Merinfo T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('merinfo_personer_saved')->label('Merinfo S')->numeric()->sortable()->alignCenter(),

                TextColumn::make('ratsit_personer_total')->label('Ratsit T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('ratsit_personer_saved')->label('Ratsit S')->numeric()->sortable()->alignCenter(),

                TextColumn::make('hitta_personer_total')->label('Hitta T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('hitta_personer_saved')->label('Hitta S')->numeric()->sortable()->alignCenter(),

                TextColumn::make('post_nummer_personer_total')->label('PostNr T')->numeric()->sortable()->alignCenter(),
                TextColumn::make('post_nummer_personer_saved')->label('PostNr S')->numeric()->sortable()->alignCenter(),

                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active Status'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
