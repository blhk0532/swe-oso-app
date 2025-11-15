<?php

namespace App\Filament\Resources\PostNummerQueues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostNummerQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('post_nummer')->label('Post Nr')->searchable()->sortable(),
            TextColumn::make('post_ort')->label('Post Ort')->searchable()->sortable(),
            TextColumn::make('post_lan')->label('Post Län')->searchable()->sortable(),
            TextColumn::make('merinfo_personer_saved')->label('M P S')->numeric()->sortable()->alignCenter(),
            TextColumn::make('ratsit_personer_saved')->label('R P S')->numeric()->sortable()->alignCenter(),
            TextColumn::make('hitta_personer_saved')->label('H P S')->numeric()->sortable()->alignCenter(),
            TextColumn::make('post_nummer_personer_saved')->label('PN P S')->numeric()->sortable()->alignCenter(),
            IconColumn::make('merinfo_queued')->label('M Q')->boolean(),
            IconColumn::make('ratsit_queued')->label('R Q')->boolean(),
            IconColumn::make('hitta_queued')->label('H Q')->boolean(),
            IconColumn::make('post_nummer_queued')->label('PN Q')->boolean(),
            IconColumn::make('is_active')->label('Active')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('merinfo_queued')->label('Merinfo Queued'),
                TernaryFilter::make('ratsit_queued')->label('Ratsit Queued'),
                TernaryFilter::make('hitta_queued')->label('Hitta Queued'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
