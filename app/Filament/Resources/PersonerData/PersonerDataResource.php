<?php

namespace App\Filament\Resources\PersonerData;

use App\Filament\Resources\PersonerData\Pages\CreatePersonerData;
use App\Filament\Resources\PersonerData\Pages\EditPersonerData;
use App\Filament\Resources\PersonerData\Pages\ListPersonerData;
use App\Filament\Resources\PersonerData\Pages\ViewPersonerData;
use App\Filament\Resources\PersonerData\Schemas\PersonerDataForm;
use App\Filament\Resources\PersonerData\Schemas\PersonerDataInfolist;
use App\Filament\Resources\PersonerData\Tables\PersonerDataTable;
use App\Models\PersonerData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PersonerDataResource extends Resource
{
    protected static ?string $model = PersonerData::class;

    protected static ?int $navigationSort = 4;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PersonerDataForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PersonerDataInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonerDataTable::configure($table);
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
            'index' => ListPersonerData::route('/'),
            'create' => CreatePersonerData::route('/create'),
            'view' => ViewPersonerData::route('/{record}'),
            'edit' => EditPersonerData::route('/{record}/edit'),
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
}
