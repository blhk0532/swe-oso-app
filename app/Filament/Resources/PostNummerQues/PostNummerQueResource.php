<?php

namespace App\Filament\Resources\PostNummerQues;

use App\Filament\Resources\PostNummerQues\Pages\CreatePostNummerQue;
use App\Filament\Resources\PostNummerQues\Pages\EditPostNummerQue;
use App\Filament\Resources\PostNummerQues\Pages\ListPostNummerQues;
use App\Filament\Resources\PostNummerQues\Schemas\PostNummerQueForm;
use App\Filament\Resources\PostNummerQues\Tables\PostNummerQuesTable;
use App\Models\PostNummerQue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostNummerQueResource extends Resource
{
    protected static ?string $model = PostNummerQue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Post Nummer Queue';

    protected static ?string $modelLabel = 'Post Nummer Queue';

    protected static ?string $pluralModelLabel = 'Post Nummer Queue';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return PostNummerQueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostNummerQuesTable::configure($table);
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
            'index' => ListPostNummerQues::route('/'),
            'create' => CreatePostNummerQue::route('/create'),
            'edit' => EditPostNummerQue::route('/{record}/edit'),
        ];
    }


        public static function shouldRegisterNavigation(): bool
    {
        // Hide Blog > Posts from sidebar navigation.
        return false;
    }
}
