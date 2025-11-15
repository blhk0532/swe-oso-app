<?php

namespace App\Filament\Resources\HittaQueues;

use App\Filament\Resources\HittaQueues\Pages\CreateHittaQueue;
use App\Filament\Resources\HittaQueues\Pages\EditHittaQueue;
use App\Filament\Resources\HittaQueues\Pages\ListHittaQueues;
use App\Filament\Resources\HittaQueues\Pages\ViewHittaQueue;
use App\Filament\Resources\HittaQueues\Schemas\HittaQueueForm;
use App\Filament\Resources\HittaQueues\Schemas\HittaQueueInfolist;
use App\Filament\Resources\HittaQueues\Tables\HittaQueuesTable;
use App\Models\HittaQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HittaQueueResource extends Resource
{
    protected static ?string $model = HittaQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Hitta Queue';

    protected static ?string $modelLabel = 'Hitta Queue';

    protected static ?string $pluralModelLabel = 'Hitta Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static UnitEnum | string | null $navigationGroup = 'HITTA DATABAS';

    public static function form(Schema $schema): Schema
    {
        return HittaQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HittaQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HittaQueuesTable::configure($table);
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
            'index' => ListHittaQueues::route('/'),
            'create' => CreateHittaQueue::route('/create'),
            'view' => ViewHittaQueue::route('/{record}'),
            'edit' => EditHittaQueue::route('/{record}/edit'),
        ];
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        $count = HittaQueue::query()
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
