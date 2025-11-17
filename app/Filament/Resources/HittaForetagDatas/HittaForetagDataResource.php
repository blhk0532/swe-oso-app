<?php

namespace App\Filament\Resources\HittaForetagDatas;

use App\Filament\Resources\HittaDatas\Schemas\HittaDataForm;
use App\Filament\Resources\HittaForetagDatas\Pages\CreateHittaForetagData;
use App\Filament\Resources\HittaForetagDatas\Pages\EditHittaForetagData;
use App\Filament\Resources\HittaForetagDatas\Pages\ListHittaForetagDatas;
use App\Filament\Resources\HittaForetagDatas\Tables\HittaForetagDatasTable;
use App\Models\HittaForetagData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HittaForetagDataResource extends Resource
{
    protected static ?string $model = HittaForetagData::class;

    protected static ?string $navigationLabel = 'Hitta Företag Data';

    protected static ?string $modelLabel = 'Hitta Företag Data';

    protected static ?string $pluralModelLabel = 'Hitta Företag Data';

    protected static UnitEnum | string | null $navigationGroup = 'HITTA DATABAS';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'databaser/hitta-foretag-data';

    public static function form(Schema $schema): Schema
    {
        return HittaDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HittaForetagDatasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHittaForetagDatas::route('/'),
            'create' => CreateHittaForetagData::route('/create'),
            'edit' => EditHittaForetagData::route('/{record}/edit'),
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
