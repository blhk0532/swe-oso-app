<?php

namespace App\Filament\Resources\RatsitPersonKommuners;

use App\Filament\Resources\RatsitPersonKommuners\Pages\CreateRatsitPersonKommuner;
use App\Filament\Resources\RatsitPersonKommuners\Pages\EditRatsitPersonKommuner;
use App\Filament\Resources\RatsitPersonKommuners\Pages\ListRatsitPersonKommuners;
use App\Filament\Resources\RatsitPersonKommuners\Pages\ViewRatsitPersonKommuner;
use App\Filament\Resources\RatsitPersonKommuners\Schemas\RatsitPersonKommunerForm;
use App\Filament\Resources\RatsitPersonKommuners\Schemas\RatsitPersonKommunerInfolist;
use App\Filament\Resources\RatsitPersonKommuners\Tables\RatsitPersonKommunersTable;
use App\Models\RatsitPersonKommuner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitPersonKommunerResource extends Resource
{
    protected static ?string $model = RatsitPersonKommuner::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABASER';

    public static function form(Schema $schema): Schema
    {
        return RatsitPersonKommunerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitPersonKommunerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitPersonKommunersTable::configure($table);
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
            'index' => ListRatsitPersonKommuners::route('/'),
            'create' => CreateRatsitPersonKommuner::route('/create'),
            'view' => ViewRatsitPersonKommuner::route('/{record}'),
            'edit' => EditRatsitPersonKommuner::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
