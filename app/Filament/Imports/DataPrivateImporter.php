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
            ImportColumn::make('bo_gatuadress')
                ->label('Address')
                ->example('Main Street 123'),

            ImportColumn::make('bo_postnummer')
                ->label('Postal Code')
                ->example('12345'),

            ImportColumn::make('bo_postort')
                ->label('City')
                ->example('Stockholm'),

            ImportColumn::make('bo_forsamling')
                ->label('Parish')
                ->example('Parish Name'),

            ImportColumn::make('bo_kommun')
                ->label('Municipality')
                ->example('Municipality Name'),

            ImportColumn::make('bo_lan')
                ->label('State')
                ->example('Stockholm County'),

            ImportColumn::make('ps_fodelsedag')
                ->label('Date of Birth')
                ->example('1990-01-15'),

            ImportColumn::make('ps_personnummer')
                ->label('Social Security Number')
                ->example('199001151234'),

            ImportColumn::make('ps_alder')
                ->label('Age')
                ->example('34'),

            ImportColumn::make('ps_kon')
                ->label('Sex')
                ->example('M'),

            ImportColumn::make('ps_civilstand')
                ->label('Marital Status')
                ->example('married'),

            ImportColumn::make('ps_fornamn')
                ->label('First Name')
                ->example('John'),

            ImportColumn::make('ps_efternamn')
                ->label('Last Name')
                ->example('Doe'),

            ImportColumn::make('ps_personnamn')
                ->label('Full Name')
                ->example('John Doe'),

            ImportColumn::make('ps_telefon')
                ->label('Phone Numbers (JSON array)')
                ->example('["+46123456789","+46987654321"]'),

            ImportColumn::make('ps_epost_adress')
                ->label('Email Addresses (JSON array)')
                ->example('["john@example.com"]'),

            ImportColumn::make('bo_agandeform')
                ->label('Form of Ownership')
                ->example('Owned'),

            ImportColumn::make('bo_bostadstyp')
                ->label('Housing Type')
                ->example('Apartment'),

            ImportColumn::make('bo_boarea')
                ->label('Living Area')
                ->example('75'),

            ImportColumn::make('bo_byggar')
                ->label('Year of Construction')
                ->example('1990'),

            ImportColumn::make('bo_fastighet')
                ->label('Fastighetsbetäckning')
                ->example('ABC 123'),

            ImportColumn::make('bo_personer')
                ->label('Persons at Address (JSON array)')
                ->example('["John Doe","Jane Doe"]'),

            ImportColumn::make('bo_foretag')
                ->label('Companies at Address (JSON array)')
                ->example('["Company ABC"]'),

            ImportColumn::make('bo_grannar')
                ->label('Neighbors (JSON array)')
                ->example('["Neighbor Name"]'),

            ImportColumn::make('bo_fordon')
                ->label('Vehicles (JSON array)')
                ->example('[{"type":"Car","registration":"ABC123"}]'),

            ImportColumn::make('bo_hundar')
                ->label('Dogs (JSON array)')
                ->example('["Buddy"]'),

            ImportColumn::make('bo_longitude')
                ->label('Longitude')
                ->numeric()
                ->example('18.0686'),

            ImportColumn::make('bo_latitud')
                ->label('Latitude')
                ->numeric()
                ->example('59.3293'),

            ImportColumn::make('ps_bolagsengagemang')
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
            'ps_telefon',
            'ps_epost_adress',
            'bo_personer',
            'bo_foretag',
            'bo_grannar',
            'bo_fordon',
            'bo_hundar',
            'ps_bolagsengagemang',
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
