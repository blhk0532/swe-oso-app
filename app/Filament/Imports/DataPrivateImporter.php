<?php

namespace App\Filament\Imports;

use App\Models\DataPrivate;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DataPrivateImporter extends Importer
{
    protected static ?string $model = DataPrivate::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('gatuadress')
                ->label('Address')
                ->example('Main Street 123'),

            ImportColumn::make('postnummer')
                ->label('Postal Code')
                ->example('12345'),

            ImportColumn::make('postort')
                ->label('City')
                ->example('Stockholm'),

            ImportColumn::make('forsamling')
                ->label('Parish')
                ->example('Parish Name'),

            ImportColumn::make('kommun')
                ->label('Municipality')
                ->example('Municipality Name'),

            ImportColumn::make('lan')
                ->label('State')
                ->example('Stockholm County'),

            ImportColumn::make('fodelsedag')
                ->label('Date of Birth')
                ->example('1990-01-15'),

            ImportColumn::make('personnummer')
                ->label('Social Security Number')
                ->example('199001151234'),

            ImportColumn::make('alder')
                ->label('Age')
                ->example('34'),

            ImportColumn::make('kon')
                ->label('Sex')
                ->example('M'),

            ImportColumn::make('civilstand')
                ->label('Marital Status')
                ->example('married'),

            ImportColumn::make('fornamn')
                ->label('First Name')
                ->example('John'),

            ImportColumn::make('efternamn')
                ->label('Last Name')
                ->example('Doe'),

            ImportColumn::make('personnamn')
                ->label('Full Name')
                ->example('John Doe'),

            ImportColumn::make('telefon')
                ->label('Phone Numbers (JSON array)')
                ->example('["+46123456789","+46987654321"]'),

            ImportColumn::make('epost_adress')
                ->label('Email Addresses (JSON array)')
                ->example('["john@example.com"]'),

            ImportColumn::make('agandeform')
                ->label('Form of Ownership')
                ->example('Owned'),

            ImportColumn::make('bostadstyp')
                ->label('Housing Type')
                ->example('Apartment'),

            ImportColumn::make('boarea')
                ->label('Living Area')
                ->example('75'),

            ImportColumn::make('byggar')
                ->label('Year of Construction')
                ->example('1990'),

            ImportColumn::make('fastighet')
                ->label('Fastighetsbetäckning')
                ->example('ABC 123'),

            ImportColumn::make('personer')
                ->label('Persons at Address (JSON array)')
                ->example('["John Doe","Jane Doe"]'),

            ImportColumn::make('foretag')
                ->label('Companies at Address (JSON array)')
                ->example('["Company ABC"]'),

            ImportColumn::make('grannar')
                ->label('Neighbors (JSON array)')
                ->example('["Neighbor Name"]'),

            ImportColumn::make('fordon')
                ->label('Vehicles (JSON array)')
                ->example('[{"type":"Car","registration":"ABC123"}]'),

            ImportColumn::make('hundar')
                ->label('Dogs (JSON array)')
                ->example('["Buddy"]'),

            ImportColumn::make('longitude')
                ->label('Longitude')
                ->numeric()
                ->example('18.0686'),

            ImportColumn::make('latitud')
                ->label('Latitude')
                ->numeric()
                ->example('59.3293'),

            ImportColumn::make('bolagsengagemang')
                ->label('Corporate Commitments (JSON array)')
                ->example('[{"company":"ABC Corp","role":"Director"}]'),

            ImportColumn::make('is_active')
                ->label('Active')
                ->boolean()
                ->default(true)
                ->example('yes'),
        ];
    }

    public function resolveRecord(): DataPrivate
    {
        return new DataPrivate;
    }

    public function mutateValues(array $values): array
    {
        // Convert JSON strings to arrays for JSONB fields
        $jsonFields = [
            'telefon',
            'epost_adress',
            'personer',
            'foretag',
            'grannar',
            'fordon',
            'hundar',
            'bolagsengagemang',
        ];

        foreach ($jsonFields as $field) {
            if (isset($values[$field]) && is_string($values[$field])) {
                $decoded = json_decode($values[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $values[$field] = $decoded;
                } else {
                    $values[$field] = [];
                }
            } elseif (! isset($values[$field])) {
                $values[$field] = [];
            }
        }

        return $values;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your data private import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
