<?php

namespace App\Filament\Exports;

use App\Models\DataPrivate;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class DataPrivateExporter extends Exporter
{
    protected static ?string $model = DataPrivate::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('bo_gatuadress')
                ->label('Address'),

            ExportColumn::make('bo_postnummer')
                ->label('Postal Code'),

            ExportColumn::make('bo_postort')
                ->label('City'),

            ExportColumn::make('bo_forsamling')
                ->label('Parish'),

            ExportColumn::make('bo_kommun')
                ->label('Municipality'),

            ExportColumn::make('bo_lan')
                ->label('State'),

            ExportColumn::make('ps_fodelsedag')
                ->label('Date of Birth')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d') ?? ''),

            ExportColumn::make('ps_personnummer')
                ->label('Social Security Number'),

            ExportColumn::make('ps_alder')
                ->label('Age'),

            ExportColumn::make('ps_kon')
                ->label('Sex'),

            ExportColumn::make('ps_civilstand')
                ->label('Marital Status'),

            ExportColumn::make('ps_fornamn')
                ->label('First Name'),

            ExportColumn::make('ps_efternamn')
                ->label('Last Name'),

            ExportColumn::make('ps_personnamn')
                ->label('Full Name'),

            ExportColumn::make('ps_telefon')
                ->label('Phone Numbers')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('ps_epost_adress')
                ->label('Email Addresses')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('bo_agandeform')
                ->label('Form of Ownership'),

            ExportColumn::make('bo_bostadstyp')
                ->label('Housing Type'),

            ExportColumn::make('bo_boarea')
                ->label('Living Area'),

            ExportColumn::make('bo_byggar')
                ->label('Year of Construction'),

            ExportColumn::make('bo_fastighet')
                ->label('Fastighetsbetäckning'),

            ExportColumn::make('bo_personer')
                ->label('Persons at Address')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('bo_foretag')
                ->label('Companies at Address')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('bo_grannar')
                ->label('Neighbors')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('bo_fordon')
                ->label('Vehicles')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('bo_hundar')
                ->label('Dogs')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('bo_longitude')
                ->label('Longitude'),

            ExportColumn::make('bo_latitud')
                ->label('Latitude'),

            ExportColumn::make('ps_bolagsengagemang')
                ->label('Corporate Commitments')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('is_active')
                ->label('Active')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),

            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s') ?? ''),

            ExportColumn::make('updated_at')
                ->label('Updated At')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your data private export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
