<?php

namespace App\Filament\Resources\RatsitPersonPostorters;

use App\Filament\Resources\RatsitPersonPostorters\Pages\CreateRatsitPersonPostorter;
use App\Filament\Resources\RatsitPersonPostorters\Pages\EditRatsitPersonPostorter;
use App\Filament\Resources\RatsitPersonPostorters\Pages\ListRatsitPersonPostorters;
use App\Filament\Resources\RatsitPersonPostorters\Pages\ViewRatsitPersonPostorter;
use App\Filament\Resources\RatsitPersonPostorters\Schemas\RatsitPersonPostorterForm;
use App\Filament\Resources\RatsitPersonPostorters\Schemas\RatsitPersonPostorterInfolist;
use App\Filament\Resources\RatsitPersonPostorters\Tables\RatsitPersonPostortersTable;
use App\Models\RatsitPersonPostorter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitPersonPostorterResource extends Resource
{
    protected static ?string $model = RatsitPersonPostorter::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABASER';

    public static function form(Schema $schema): Schema
    {
        return RatsitPersonPostorterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitPersonPostorterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitPersonPostortersTable::configure($table);
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
            'index' => ListRatsitPersonPostorters::route('/'),
            'create' => CreateRatsitPersonPostorter::route('/create'),
            'view' => ViewRatsitPersonPostorter::route('/{record}'),
            'edit' => EditRatsitPersonPostorter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
