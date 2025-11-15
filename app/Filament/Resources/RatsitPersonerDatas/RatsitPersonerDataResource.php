<?php

namespace App\Filament\Resources\RatsitPersonerDatas;

use App\Filament\Resources\RatsitDatas\Schemas\RatsitDataForm;
use App\Filament\Resources\RatsitDatas\Tables\RatsitDatasTable;
use App\Filament\Resources\RatsitPersonerDatas\Pages\CreateRatsitPersonerData;
use App\Filament\Resources\RatsitPersonerDatas\Pages\EditRatsitPersonerData;
use App\Filament\Resources\RatsitPersonerDatas\Pages\ListRatsitPersonerDatas;
use App\Models\RatsitData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RatsitPersonerDataResource extends Resource
{
    protected static ?string $model = RatsitData::class;

    protected static ?string $navigationLabel = 'Ratsit Personer Data';

    protected static ?string $modelLabel = 'Ratsit Personer Data';

    protected static ?string $pluralModelLabel = 'Ratsit Personer Data';

    protected static string | UnitEnum | null $navigationGroup = 'RATSIT DATABAS';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'databaser/ratsit-personer-data';

    public static function form(Schema $schema): Schema
    {
        return RatsitDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitDatasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatsitPersonerDatas::route('/'),
            'create' => CreateRatsitPersonerData::route('/create'),
            'edit' => EditRatsitPersonerData::route('/{record}/edit'),
        ];
    }
}
