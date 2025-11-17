<?php

namespace App\Filament\Resources\MerinfoQueues;

use App\Filament\Resources\MerinfoQueues\Pages\CreateMerinfoQueue;
use App\Filament\Resources\MerinfoQueues\Pages\EditMerinfoQueue;
use App\Filament\Resources\MerinfoQueues\Pages\ListMerinfoQueues;
use App\Filament\Resources\MerinfoQueues\Pages\ViewMerinfoQueue;
use App\Filament\Resources\MerinfoQueues\Schemas\MerinfoQueueForm;
use App\Filament\Resources\MerinfoQueues\Schemas\MerinfoQueueInfolist;
use App\Filament\Resources\MerinfoQueues\Tables\MerinfoQueuesTable;
use App\Models\MerinfoQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MerinfoQueueResource extends Resource
{
    protected static ?string $model = MerinfoQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Merinfo Queue';

    protected static ?string $modelLabel = 'Merinfo Queue';

    protected static ?string $pluralModelLabel = 'Merinfo Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static ?int $navigationSort = 0;

    protected static UnitEnum | string | null $navigationGroup = 'MERINFO DATABAS';

    public static function form(Schema $schema): Schema
    {
        return MerinfoQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerinfoQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfoQueuesTable::configure($table);
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
            'index' => ListMerinfoQueues::route('/'),
            'create' => CreateMerinfoQueue::route('/create'),
            'view' => ViewMerinfoQueue::route('/{record}'),
            'edit' => EditMerinfoQueue::route('/{record}/edit'),
        ];
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        $count = MerinfoQueue::query()
    //            ->where(function ($q) {
    //                $q->where('personer_queued', true)
    //                    ->orWhere('foretag_queued', true);
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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
