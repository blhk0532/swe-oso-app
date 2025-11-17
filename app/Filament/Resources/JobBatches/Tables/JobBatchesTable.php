<?php

namespace App\Filament\Resources\JobBatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Batch ID')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('total_jobs')
                    ->label('Total Jobs')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pending_jobs')
                    ->label('Pending')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('failed_jobs')
                    ->label('Failed')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('failed_job_ids')
                    ->label('Failed Job IDs')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return json_encode($record->failed_job_ids, JSON_PRETTY_PRINT);
                    })
                    ->placeholder('None'),
                TextColumn::make('options')
                    ->label('Options')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->options ? json_encode($record->options, JSON_PRETTY_PRINT) : 'None';
                    })
                    ->placeholder('None'),
                TextColumn::make('cancelled_at')
                    ->label('Cancelled At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not cancelled'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('finished_at')
                    ->label('Finished At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not finished'),
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
