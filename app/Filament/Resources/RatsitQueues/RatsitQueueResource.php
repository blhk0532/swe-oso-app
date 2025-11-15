<?php

namespace App\Filament\Resources\RatsitQueues;

use App\Filament\Resources\RatsitQueues\Pages\CreateRatsitQueue;
use App\Filament\Resources\RatsitQueues\Pages\EditRatsitQueue;
use App\Filament\Resources\RatsitQueues\Pages\ListRatsitQueues;
use App\Filament\Resources\RatsitQueues\Pages\ViewRatsitQueue;
use App\Filament\Resources\RatsitQueues\Schemas\RatsitQueueForm;
use App\Filament\Resources\RatsitQueues\Schemas\RatsitQueueInfolist;
use App\Filament\Resources\RatsitQueues\Tables\RatsitQueuesTable;
use App\Models\RatsitQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitQueueResource extends Resource
{
    protected static ?string $model = RatsitQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Ratsit Queue';

    protected static ?string $modelLabel = 'Ratsit Queue';

    protected static ?string $pluralModelLabel = 'Ratsit Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABAS';

    public static function form(Schema $schema): Schema
    {
        return RatsitQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitQueuesTable::configure($table);
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
            'index' => ListRatsitQueues::route('/'),
            'create' => CreateRatsitQueue::route('/create'),
            'view' => ViewRatsitQueue::route('/{record}'),
            'edit' => EditRatsitQueue::route('/{record}/edit'),
        ];
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        $count = RatsitQueue::query()
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
}
