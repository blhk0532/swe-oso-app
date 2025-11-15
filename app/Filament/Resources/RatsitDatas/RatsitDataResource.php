<?php

namespace App\Filament\Resources\RatsitDatas;

use App\Filament\Resources\RatsitDatas\Pages\CreateRatsitData;
use App\Filament\Resources\RatsitDatas\Pages\EditRatsitData;
use App\Filament\Resources\RatsitDatas\Pages\ListRatsitDatas;
use App\Filament\Resources\RatsitDatas\Schemas\RatsitDataForm;
use App\Filament\Resources\RatsitDatas\Tables\RatsitDatasTable;
use App\Models\RatsitData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RatsitDataResource extends Resource
{
    protected static ?string $model = RatsitData::class;

    protected static ?string $recordTitleAttribute = 'personnamn';

    //    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'RatsitData';

    protected static string | UnitEnum | null $navigationGroup = 'PERSON DATABASER';

    protected static ?int $navigationSort = 3;

    // place resource under Databaser cluster
    protected static ?string $slug = 'databaser/ratsit-data';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return RatsitDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitDatasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatsitDatas::route('/'),
            'create' => CreateRatsitData::route('/create'),
            'edit' => EditRatsitData::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'personnamn',
            'personnummer',
            'fornamn',
            'efternamn',
            'postnummer',
            'postort',
            'kommun',
        ];
    }

    /** @return Builder<RatsitData> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var RatsitData $record */
        return [
            'Personnummer' => $record->personnummer,
            'Address' => $record->gatuadress,
            'Postort' => $record->postort,
        ];
    }
}
