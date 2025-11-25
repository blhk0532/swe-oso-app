<?php

namespace App\Filament\Resources\RatsitForetagPostorters;

use App\Filament\Resources\RatsitForetagPostorters\Pages\CreateRatsitForetagPostorter;
use App\Filament\Resources\RatsitForetagPostorters\Pages\EditRatsitForetagPostorter;
use App\Filament\Resources\RatsitForetagPostorters\Pages\ListRatsitForetagPostorters;
use App\Filament\Resources\RatsitForetagPostorters\Schemas\RatsitForetagPostorterForm;
use App\Filament\Resources\RatsitForetagPostorters\Tables\RatsitForetagPostortersTable;
use App\Models\RatsitForetagPostorter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitForetagPostorterResource extends Resource
{
    protected static ?string $model = RatsitForetagPostorter::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABASER';

    public static function form(Schema $schema): Schema
    {
        return RatsitForetagPostorterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitForetagPostortersTable::configure($table);
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
            'index' => ListRatsitForetagPostorters::route('/'),
            'create' => CreateRatsitForetagPostorter::route('/create'),
            'edit' => EditRatsitForetagPostorter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
