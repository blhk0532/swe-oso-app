<?php

namespace App\Filament\Resources\RatsitDatas\Tables;

use App\Models\RatsitData;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RatsitDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ps_personnamn')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),

                TextColumn::make('ps_personnummer')
                    ->label('Personnummer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('bo_gatuadress')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('bo_postnummer')
                    ->label('Postnummer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bo_postort')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bo_kommun')
                    ->label('Municipality')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('bo_lan')
                    ->label('State')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ps_telefon')
                    ->label('Phone')
                    ->formatStateUsing(fn ($state) => is_array($state) && count($state) > 0 ? implode(', ', array_slice($state, 0, 2)) : '-')
                    ->searchable(query: function ($query, $state) {
                        return $query->whereJsonContains('ps_telefon', $state);
                    })
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('ps_fodelsedag')
                    ->label('Date of Birth')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('bo_agandeform')
                    ->label('Ownership')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('bo_bostadstyp')
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
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All records')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('bo_postort')
                    ->label('City')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bo_postort')
                            ->distinct()
                            ->orderBy('bo_postort')
                            ->pluck('bo_postort', 'bo_postort')
                            ->toArray();
                    }),

                SelectFilter::make('bo_kommun')
                    ->label('Municipality')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bo_kommun')
                            ->distinct()
                            ->orderBy('bo_kommun')
                            ->pluck('bo_kommun', 'bo_kommun')
                            ->toArray();
                    }),

                SelectFilter::make('bo_lan')
                    ->label('State')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bo_lan')
                            ->distinct()
                            ->orderBy('bo_lan')
                            ->pluck('bo_lan', 'bo_lan')
                            ->toArray();
                    }),

                SelectFilter::make('bo_agandeform')
                    ->label('Ownership Form')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bo_agandeform')
                            ->distinct()
                            ->orderBy('bo_agandeform')
                            ->pluck('bo_agandeform', 'bo_agandeform')
                            ->toArray();
                    }),

                SelectFilter::make('bo_bostadstyp')
                    ->label('Housing Type')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bo_bostadstyp')
                            ->distinct()
                            ->orderBy('bo_bostadstyp')
                            ->pluck('bo_bostadstyp', 'bo_bostadstyp')
                            ->toArray();
                    }),

                Filter::make('bo_postnummer')
                    ->label('Postnummer')
                    ->form([
                        TextInput::make('bo_postnummer')
                            ->label('Postnummer'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['bo_postnummer'] ?? null,
                            fn ($query, $postnummer) => $query->where('bo_postnummer', 'like', "%{$postnummer}%")
                        );
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->deferFilters()
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
