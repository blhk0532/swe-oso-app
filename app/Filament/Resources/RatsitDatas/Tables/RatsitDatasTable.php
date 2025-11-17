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

                TextColumn::make('fornamn')
                    ->label('First Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('efternamn')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('alder')
                    ->label('Age')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('kon')
                    ->label('Gender')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('fodelsedag')
                    ->label('Date of Birth')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('civilstand')
                    ->label('Civil Status')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('stjarntacken')
                    ->label('Star Sign')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

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

                TextColumn::make('forsamling')
                    ->label('Parish')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

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

                TextColumn::make('adressandring')
                    ->label('Address Change')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('telefon')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('telfonnummer')
                    ->label('Alt Phone')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('epost_adress')
                    ->label('Email')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('agandeform')
                    ->label('Ownership')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('bostadstyp')
                    ->label('Housing Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('boarea')
                    ->label('Living Area')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('byggar')
                    ->label('Build Year')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('fastighet')
                    ->label('Property')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('personer')
                    ->label('People')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('foretag')
                    ->label('Companies')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('grannar')
                    ->label('Neighbors')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('fordon')
                    ->label('Vehicles')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('hundar')
                    ->label('Dogs')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('bolagsengagemang')
                    ->label('Board Positions')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('latitud')
                    ->label('Latitude')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('longitude')
                    ->label('Longitude')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('google_maps')
                    ->label('Google Maps')
                    ->url(fn ($record) => $record->google_maps)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->formatStateUsing(fn ($state) => $state ? '🗺️ Map' : '-'),

                TextColumn::make('google_streetview')
                    ->label('Street View')
                    ->url(fn ($record) => $record->google_streetview)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->formatStateUsing(fn ($state) => $state ? '📸 View' : '-'),

                TextColumn::make('ratsit_se')
                    ->label('Ratsit Link')
                    ->url(fn ($record) => $record->ratsit_se)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->formatStateUsing(fn ($state) => $state ? '🔗 Ratsit' : '-'),

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

                TextColumn::make('updated_at')
                    ->label('Updated')
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

                SelectFilter::make('postort')
                    ->label('City')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('postort')
                            ->distinct()
                            ->orderBy('postort')
                            ->pluck('postort', 'postort')
                            ->toArray();
                    }),

                SelectFilter::make('kommun')
                    ->label('Municipality')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('kommun')
                            ->distinct()
                            ->orderBy('kommun')
                            ->pluck('kommun', 'kommun')
                            ->toArray();
                    }),

                SelectFilter::make('lan')
                    ->label('State')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('lan')
                            ->distinct()
                            ->orderBy('lan')
                            ->pluck('lan', 'lan')
                            ->toArray();
                    }),

                SelectFilter::make('agandeform')
                    ->label('Ownership Form')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('agandeform')
                            ->distinct()
                            ->orderBy('agandeform')
                            ->pluck('agandeform', 'agandeform')
                            ->toArray();
                    }),

                SelectFilter::make('bostadstyp')
                    ->label('Housing Type')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bostadstyp')
                            ->distinct()
                            ->orderBy('bostadstyp')
                            ->pluck('bostadstyp', 'bostadstyp')
                            ->toArray();
                    }),

                Filter::make('postnummer')
                    ->label('Postnummer')
                    ->form([
                        TextInput::make('postnummer')
                            ->label('Postnummer'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['postnummer'] ?? null,
                            fn ($query, $postnummer) => $query->where('postnummer', 'like', "%{$postnummer}%")
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
