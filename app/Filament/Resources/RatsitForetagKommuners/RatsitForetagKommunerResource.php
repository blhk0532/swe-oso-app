<?php

namespace App\Filament\Resources\RatsitForetagKommuners;

use App\Filament\Resources\RatsitForetagKommuners\Pages\CreateRatsitForetagKommuner;
use App\Filament\Resources\RatsitForetagKommuners\Pages\EditRatsitForetagKommuner;
use App\Filament\Resources\RatsitForetagKommuners\Pages\ListRatsitForetagKommuners;
use App\Filament\Resources\RatsitForetagKommuners\Pages\ViewRatsitForetagKommuner;
use App\Filament\Resources\RatsitForetagKommuners\Schemas\RatsitForetagKommunerForm;
use App\Filament\Resources\RatsitForetagKommuners\Schemas\RatsitForetagKommunerInfolist;
use App\Filament\Resources\RatsitForetagKommuners\Tables\RatsitForetagKommunersTable;
use App\Models\RatsitForetagKommuner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitForetagKommunerResource extends Resource
{
    protected static ?string $model = RatsitForetagKommuner::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABASER';

    public static function form(Schema $schema): Schema
    {
        return RatsitForetagKommunerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitForetagKommunerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitForetagKommunersTable::configure($table);
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
            'index' => ListRatsitForetagKommuners::route('/'),
            'create' => CreateRatsitForetagKommuner::route('/create'),
            'view' => ViewRatsitForetagKommuner::route('/{record}'),
            'edit' => EditRatsitForetagKommuner::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
