<?php

namespace App\Filament\Resources\MerinfoDatas;

use App\Filament\Resources\MerinfoDatas\Pages\CreateMerinfoData;
use App\Filament\Resources\MerinfoDatas\Pages\EditMerinfoData;
use App\Filament\Resources\MerinfoDatas\Pages\ListMerinfoDatas;
use App\Filament\Resources\MerinfoDatas\Schemas\MerinfoDataForm;
use App\Filament\Resources\MerinfoDatas\Tables\MerinfoDatasTable;
use App\Models\MerinfoData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MerinfoDataResource extends Resource
{
    protected static ?string $model = MerinfoData::class;

    //    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Merinfo Data';

    protected static string | UnitEnum | null $navigationGroup = 'MERINFO DATABAS';

    protected static ?string $modelLabel = 'Merinfo Data';

    protected static ?string $pluralModelLabel = 'Merinfo data';

    protected static ?int $navigationSort = 1;

    // place resource under Databaser cluster
    protected static ?string $slug = 'databaser/merinfo-data';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return MerinfoDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfoDatasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerinfoDatas::route('/'),
            'create' => CreateMerinfoData::route('/create'),
            'edit' => EditMerinfoData::route('/{record}/edit'),
        ];
    }
}
