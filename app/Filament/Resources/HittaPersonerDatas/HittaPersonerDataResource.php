<?php

namespace App\Filament\Resources\HittaPersonerDatas;

use App\Filament\Resources\HittaDatas\Schemas\HittaDataForm;
use App\Filament\Resources\HittaPersonerDatas\Pages\CreateHittaPersonerData;
use App\Filament\Resources\HittaPersonerDatas\Pages\EditHittaPersonerData;
use App\Filament\Resources\HittaPersonerDatas\Pages\ListHittaPersonerDatas;
use App\Filament\Resources\HittaPersonerDatas\Tables\HittaDatasTable;
use App\Models\HittaPersonerData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HittaPersonerDataResource extends Resource
{
    protected static ?string $model = HittaPersonerData::class;

    protected static ?string $navigationLabel = 'Hitta Personer Data';

    protected static ?string $modelLabel = 'Hitta Personer Data';

    protected static ?string $pluralModelLabel = 'Hitta Personer Data';

    protected static UnitEnum | string | null $navigationGroup = 'HITTA DATABAS';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'databaser/hitta-personer-data';

    public static function form(Schema $schema): Schema
    {
        return HittaDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HittaDatasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHittaPersonerDatas::route('/'),
            'create' => CreateHittaPersonerData::route('/create'),
            'edit' => EditHittaPersonerData::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
