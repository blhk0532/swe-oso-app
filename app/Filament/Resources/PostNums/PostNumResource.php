<?php

namespace App\Filament\Resources\PostNums;

use App\Filament\Resources\PostNums\Pages\CreatePostNum;
use App\Filament\Resources\PostNums\Pages\EditPostNum;
use App\Filament\Resources\PostNums\Pages\ListPostNums;
use App\Filament\Resources\PostNums\Pages\ViewPostNum;
use App\Filament\Resources\PostNums\Schemas\PostNumForm;
use App\Filament\Resources\PostNums\Schemas\PostNumInfolist;
use App\Filament\Resources\PostNums\Tables\PostNumsTable;
use App\Models\PostNum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostNumResource extends Resource
{
    protected static ?string $model = PostNum::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PostNumForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostNumInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostNumsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostNums::route('/'),
            'create' => CreatePostNum::route('/create'),
            'view' => ViewPostNum::route('/{record}'),
            'edit' => EditPostNum::route('/{record}/edit'),
        ];
    }
}
