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

            ExportColumn::make('gatuadress')
                ->label('Address'),

            ExportColumn::make('postnummer')
                ->label('Postal Code'),

            ExportColumn::make('postort')
                ->label('City'),

            ExportColumn::make('forsamling')
                ->label('Parish'),

            ExportColumn::make('kommun')
                ->label('Municipality'),

            ExportColumn::make('lan')
                ->label('State'),

            ExportColumn::make('fodelsedag')
                ->label('Date of Birth')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d') ?? ''),

            ExportColumn::make('personnummer')
                ->label('Social Security Number'),

            ExportColumn::make('alder')
                ->label('Age'),

            ExportColumn::make('kon')
                ->label('Sex'),

            ExportColumn::make('civilstand')
                ->label('Marital Status'),

            ExportColumn::make('fornamn')
                ->label('First Name'),

            ExportColumn::make('efternamn')
                ->label('Last Name'),

            ExportColumn::make('personnamn')
                ->label('Full Name'),

            ExportColumn::make('telefon')
                ->label('Phone Numbers')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('epost_adress')
                ->label('Email Addresses')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('agandeform')
                ->label('Form of Ownership'),

            ExportColumn::make('bostadstyp')
                ->label('Housing Type'),

            ExportColumn::make('boarea')
                ->label('Living Area'),

            ExportColumn::make('byggar')
                ->label('Year of Construction'),

            ExportColumn::make('fastighet')
                ->label('Fastighetsbetäckning'),

            ExportColumn::make('personer')
                ->label('Persons at Address')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('foretag')
                ->label('Companies at Address')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('grannar')
                ->label('Neighbors')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('fordon')
                ->label('Vehicles')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('hundar')
                ->label('Dogs')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : ''),

            ExportColumn::make('longitude')
                ->label('Longitude'),

            ExportColumn::make('latitud')
                ->label('Latitude'),

            ExportColumn::make('bolagsengagemang')
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
