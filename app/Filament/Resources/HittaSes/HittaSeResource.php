<?php

namespace App\Filament\Resources\HittaSes;

use App\Filament\Resources\HittaSes\Pages\CreateHittaSe;
use App\Filament\Resources\HittaSes\Pages\EditHittaSe;
use App\Filament\Resources\HittaSes\Pages\ListHittaSes;
use App\Filament\Resources\HittaSes\Schemas\HittaSeForm;
use App\Filament\Resources\HittaSes\Tables\HittaSesTable;
use App\Models\HittaSe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HittaSeResource extends Resource
{
    protected static ?string $model = HittaSe::class;

    //    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Hitta Alla';

    protected static UnitEnum | string | null $navigationGroup = 'HITTA DATABAS';

    // Icon shown before the navigation group title (Filament v4+)
    protected static string | UnitEnum | null $navigationGroupIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'Hitta';

    protected static ?string $pluralModelLabel = 'Hitta Alla';

    protected static ?int $navigationSort = 7;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return HittaSeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HittaSesTable::configure($table);
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
            'index' => ListHittaSes::route('/'),
            'create' => CreateHittaSe::route('/create'),
            'edit' => EditHittaSe::route('/{record}/edit'),
        ];
    }
}
