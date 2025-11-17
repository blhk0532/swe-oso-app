<?php

namespace App\Filament\Resources\MerinfoForetagDatas;

use App\Filament\Resources\MerinfoDatas\Schemas\MerinfoDataForm;
use App\Filament\Resources\MerinfoForetagDatas\Pages\CreateMerinfoForetagData;
use App\Filament\Resources\MerinfoForetagDatas\Pages\EditMerinfoForetagData;
use App\Filament\Resources\MerinfoForetagDatas\Pages\ListMerinfoForetagDatas;
use App\Filament\Resources\MerinfoForetagDatas\Tables\MerinfoForetagDatasTable;
use App\Models\MerinfoForetagData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MerinfoForetagDataResource extends Resource
{
    protected static ?string $model = MerinfoForetagData::class;

    protected static ?string $navigationLabel = 'Merinfo Företag Data';

    protected static ?int $navigationSort = 5;

    protected static UnitEnum | string | null $navigationGroup = 'MERINFO DATABAS';

    protected static ?string $slug = 'databaser/merinfo-foretag-data';

    public static function form(Schema $schema): Schema
    {
        return MerinfoDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfoForetagDatasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerinfoForetagDatas::route('/'),
            'create' => CreateMerinfoForetagData::route('/create'),
            'edit' => EditMerinfoForetagData::route('/{record}/edit'),
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
