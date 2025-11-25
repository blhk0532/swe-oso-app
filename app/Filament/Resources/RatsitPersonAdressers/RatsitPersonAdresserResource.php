<?php

namespace App\Filament\Resources\RatsitPersonAdressers;

use App\Filament\Resources\RatsitPersonAdressers\Pages\CreateRatsitPersonAdresser;
use App\Filament\Resources\RatsitPersonAdressers\Pages\EditRatsitPersonAdresser;
use App\Filament\Resources\RatsitPersonAdressers\Pages\ListRatsitPersonAdressers;
use App\Filament\Resources\RatsitPersonAdressers\Pages\ViewRatsitPersonAdresser;
use App\Filament\Resources\RatsitPersonAdressers\Schemas\RatsitPersonAdresserForm;
use App\Filament\Resources\RatsitPersonAdressers\Schemas\RatsitPersonAdresserInfolist;
use App\Filament\Resources\RatsitPersonAdressers\Tables\RatsitPersonAdressersTable;
use App\Models\RatsitPersonAdresser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitPersonAdresserResource extends Resource
{
    protected static ?string $model = RatsitPersonAdresser::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABASER';

    public static function form(Schema $schema): Schema
    {
        return RatsitPersonAdresserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitPersonAdresserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitPersonAdressersTable::configure($table);
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
            'index' => ListRatsitPersonAdressers::route('/'),
            'create' => CreateRatsitPersonAdresser::route('/create'),
            'view' => ViewRatsitPersonAdresser::route('/{record}'),
            'edit' => EditRatsitPersonAdresser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
