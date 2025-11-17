<?php

namespace App\Filament\Resources\PostNummerPersonerQueues;

use App\Filament\Resources\PostNummerPersonerQueues\Pages\CreatePostNummerPersonerQueue;
use App\Filament\Resources\PostNummerPersonerQueues\Pages\EditPostNummerPersonerQueue;
use App\Filament\Resources\PostNummerPersonerQueues\Pages\ListPostNummerPersonerQueues;
use App\Filament\Resources\PostNummerPersonerQueues\Pages\ViewPostNummerPersonerQueue;
use App\Filament\Resources\PostNummerPersonerQueues\Schemas\PostNummerPersonerQueueForm;
use App\Filament\Resources\PostNummerPersonerQueues\Schemas\PostNummerPersonerQueueInfolist;
use App\Filament\Resources\PostNummerPersonerQueues\Tables\PostNummerPersonerQueuesTable;
use App\Models\PostNummerPersonerQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostNummerPersonerQueueResource extends Resource
{
    protected static ?string $model = PostNummerPersonerQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Postnummer Personer Queue';

    protected static ?string $modelLabel = 'Postnummer Personer Queue';

    protected static ?string $pluralModelLabel = 'Postnummer Personer Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static UnitEnum | string | null $navigationGroup = 'POST NUMMER';

    public static function form(Schema $schema): Schema
    {
        return PostNummerPersonerQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostNummerPersonerQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostNummerPersonerQueuesTable::configure($table);
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
            'index' => ListPostNummerPersonerQueues::route('/'),
            'create' => CreatePostNummerPersonerQueue::route('/create'),
            'view' => ViewPostNummerPersonerQueue::route('/{record}'),
            'edit' => EditPostNummerPersonerQueue::route('/{record}/edit'),
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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
