<?php

namespace App\Filament\Resources\PostNummers;

use App\Filament\Resources\PostNummers\Pages\CreatePostNummer;
use App\Filament\Resources\PostNummers\Pages\EditPostNummer;
use App\Filament\Resources\PostNummers\Pages\ListPostNummers;
use App\Filament\Resources\PostNummers\Schemas\PostNummerForm;
use App\Filament\Resources\PostNummers\Tables\PostNummersTable;
use App\Models\PostNummer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostNummerResource extends Resource
{
    protected static ?string $model = PostNummer::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Post Nummer';

    protected static ?string $modelLabel = 'Post Nummer';

    protected static ?string $pluralModelLabel = 'Post Nummer';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return PostNummerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostNummersTable::configure($table);
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
            'index' => ListPostNummers::route('/'),
            'create' => CreatePostNummer::route('/create'),
            'edit' => EditPostNummer::route('/{record}/edit'),
        ];
    }
}
