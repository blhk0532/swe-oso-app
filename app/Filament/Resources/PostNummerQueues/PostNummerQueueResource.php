<?php

namespace App\Filament\Resources\PostNummerQueues;

use App\Filament\Resources\PostNummerQueues\Pages\CreatePostNummerQueue;
use App\Filament\Resources\PostNummerQueues\Pages\EditPostNummerQueue;
use App\Filament\Resources\PostNummerQueues\Pages\ListPostNummerQueues;
use App\Filament\Resources\PostNummerQueues\Pages\ViewPostNummerQueue;
use App\Filament\Resources\PostNummerQueues\Schemas\PostNummerQueueForm;
use App\Filament\Resources\PostNummerQueues\Schemas\PostNummerQueueInfolist;
use App\Filament\Resources\PostNummerQueues\Tables\PostNummerQueuesTable;
use App\Models\PostNummerQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostNummerQueueResource extends Resource
{
    protected static ?string $model = PostNummerQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Postnummer Queue';

    protected static ?string $modelLabel = 'Postnummer Queue';

    protected static ?string $pluralModelLabel = 'Postnummer Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static UnitEnum | string | null $navigationGroup = 'POST NUMMER';

    public static function form(Schema $schema): Schema
    {
        return PostNummerQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostNummerQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostNummerQueuesTable::configure($table);
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
            'index' => ListPostNummerQueues::route('/'),
            'create' => CreatePostNummerQueue::route('/create'),
            'view' => ViewPostNummerQueue::route('/{record}'),
            'edit' => EditPostNummerQueue::route('/{record}/edit'),
        ];
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        $count = PostNummerQueue::query()
    //            ->where(function ($q) {
    //                $q->where('merinfo_queued', true)
    //                    ->orWhere('ratsit_queued', true)
    //                    ->orWhere('hitta_queued', true)
    //                    ->orWhere('post_nummer_queued', true);
    //            })
    //            ->count();
    //
    //        return $count > 0 ? (string) $count : null;
    //    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
