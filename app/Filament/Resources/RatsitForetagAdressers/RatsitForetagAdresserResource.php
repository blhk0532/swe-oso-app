<?php

namespace App\Filament\Resources\RatsitForetagAdressers;

use App\Filament\Resources\RatsitForetagAdressers\Pages\CreateRatsitForetagAdresser;
use App\Filament\Resources\RatsitForetagAdressers\Pages\EditRatsitForetagAdresser;
use App\Filament\Resources\RatsitForetagAdressers\Pages\ListRatsitForetagAdressers;
use App\Filament\Resources\RatsitForetagAdressers\Pages\ViewRatsitForetagAdresser;
use App\Filament\Resources\RatsitForetagAdressers\Schemas\RatsitForetagAdresserForm;
use App\Filament\Resources\RatsitForetagAdressers\Schemas\RatsitForetagAdresserInfolist;
use App\Filament\Resources\RatsitForetagAdressers\Tables\RatsitForetagAdressersTable;
use App\Models\RatsitForetagAdresser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitForetagAdresserResource extends Resource
{
    protected static ?string $model = RatsitForetagAdresser::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABASER';

    public static function form(Schema $schema): Schema
    {
        return RatsitForetagAdresserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitForetagAdresserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitForetagAdressersTable::configure($table);
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
            'index' => ListRatsitForetagAdressers::route('/'),
            'create' => CreateRatsitForetagAdresser::route('/create'),
            'view' => ViewRatsitForetagAdresser::route('/{record}'),
            'edit' => EditRatsitForetagAdresser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
