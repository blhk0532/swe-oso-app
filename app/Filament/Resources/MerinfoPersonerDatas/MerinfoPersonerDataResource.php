<?php

namespace App\Filament\Resources\MerinfoPersonerDatas;

use App\Filament\Resources\MerinfoDatas\Schemas\MerinfoDataForm;
use App\Filament\Resources\MerinfoDatas\Tables\MerinfoDatasTable;
use App\Filament\Resources\MerinfoPersonerDatas\Pages\CreateMerinfoPersonerData;
use App\Filament\Resources\MerinfoPersonerDatas\Pages\EditMerinfoPersonerData;
use App\Filament\Resources\MerinfoPersonerDatas\Pages\ListMerinfoPersonerDatas;
use App\Models\MerinfoPersonerData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MerinfoPersonerDataResource extends Resource
{
    protected static ?string $model = MerinfoPersonerData::class;

    protected static ?string $navigationLabel = 'Merinfo Personer Data';

    protected static ?string $modelLabel = 'Merinfo Personer Data';

    protected static ?string $pluralModelLabel = 'Merinfo Personer Data';

    protected static UnitEnum | string | null $navigationGroup = 'MERINFO DATABAS';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'databaser/merinfo-personer-data';

    public static function form(Schema $schema): Schema
    {
        return MerinfoDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfoDatasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerinfoPersonerDatas::route('/'),
            'create' => CreateMerinfoPersonerData::route('/create'),
            'edit' => EditMerinfoPersonerData::route('/{record}/edit'),
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
