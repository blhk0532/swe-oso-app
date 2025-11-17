<?php

namespace App\Filament\Resources\PersonerData\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PersonerDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Main columns
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('personnamn')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gatuadress')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postnummer')
                    ->sortable(),
                TextColumn::make('postort')
                    ->sortable(),

                // Hitta data columns
                TextColumn::make('hitta_data_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_personnamn')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_gatuadress')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_postnummer')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_postort')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_alder')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_kon')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_telefon')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_karta')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_link')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_bostadstyp')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hitta_bostadspris')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('hitta_is_active')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('hitta_is_telefon')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('hitta_is_hus')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('hitta_created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('hitta_updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Merinfo data columns
                TextColumn::make('merinfo_data_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_personnamn')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_alder')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_kon')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_gatuadress')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_postnummer')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_postort')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_telefon')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_karta')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_link')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_bostadstyp')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('merinfo_bostadspris')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('merinfo_is_active')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('merinfo_is_telefon')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('merinfo_is_hus')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('merinfo_created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('merinfo_updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Ratsit data columns
                TextColumn::make('ratsit_data_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_gatuadress')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_postnummer')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_postort')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_forsamling')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_kommun')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_lan')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_adressandring')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_kommun_ratsit')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_stjarntacken')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_fodelsedag')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_personnummer')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_alder')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_kon')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_civilstand')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_fornamn')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_efternamn')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_personnamn')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_agandeform')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_bostadstyp')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_boarea')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_byggar')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_fastighet')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_telfonnummer')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_epost_adress')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_personer')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_foretag')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_grannar')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_fordon')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_hundar')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_bolagsengagemang')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_longitude')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_latitud')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_google_maps')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_google_streetview')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ratsit_ratsit_se')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('ratsit_is_active')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('ratsit_is_telefon')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('ratsit_is_hus')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('ratsit_created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ratsit_updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Main table timestamps
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
