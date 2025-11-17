<?php

namespace App\Filament\Resources\RatsitForetagDatas;

use App\Filament\Resources\RatsitDatas\Schemas\RatsitDataForm;
use App\Filament\Resources\RatsitForetagDatas\Pages\CreateRatsitForetagData;
use App\Filament\Resources\RatsitForetagDatas\Pages\EditRatsitForetagData;
use App\Filament\Resources\RatsitForetagDatas\Pages\ListRatsitForetagDatas;
use App\Filament\Resources\RatsitForetagDatas\Tables\RatsitForetagDatasTable;
use App\Models\RatsitForetagData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RatsitForetagDataResource extends Resource
{
    protected static ?string $model = RatsitForetagData::class;

    protected static ?string $navigationLabel = 'Ratsit Företag Data';

    protected static ?int $navigationSort = 5;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABAS';

    protected static ?string $slug = 'databaser/ratsit-foretag-data';

    public static function form(Schema $schema): Schema
    {
        return RatsitDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitForetagDatasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatsitForetagDatas::route('/'),
            'create' => CreateRatsitForetagData::route('/create'),
            'edit' => EditRatsitForetagData::route('/{record}/edit'),
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
