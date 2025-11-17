<?php

namespace App\Filament\Resources\RatsitPersonerQueues;

use App\Filament\Resources\RatsitPersonerQueues\Pages\CreateRatsitPersonerQueue;
use App\Filament\Resources\RatsitPersonerQueues\Pages\EditRatsitPersonerQueue;
use App\Filament\Resources\RatsitPersonerQueues\Pages\ListRatsitPersonerQueues;
use App\Filament\Resources\RatsitPersonerQueues\Pages\ViewRatsitPersonerQueue;
use App\Filament\Resources\RatsitPersonerQueues\Schemas\RatsitPersonerQueueForm;
use App\Filament\Resources\RatsitPersonerQueues\Schemas\RatsitPersonerQueueInfolist;
use App\Filament\Resources\RatsitPersonerQueues\Tables\RatsitPersonerQueuesTable;
use App\Models\RatsitPersonerQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RatsitPersonerQueueResource extends Resource
{
    protected static ?string $model = RatsitPersonerQueue::class;

    protected static ?int $navigationSort = 2;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Ratsit Personer Queue';

    protected static ?string $modelLabel = 'Ratsit Personer Queue';

    protected static ?string $pluralModelLabel = 'Ratsit Personer Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABAS';

    public static function form(Schema $schema): Schema
    {
        return RatsitPersonerQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RatsitPersonerQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitPersonerQueuesTable::configure($table);
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
            'index' => ListRatsitPersonerQueues::route('/'),
            'create' => CreateRatsitPersonerQueue::route('/create'),
            'view' => ViewRatsitPersonerQueue::route('/{record}'),
            'edit' => EditRatsitPersonerQueue::route('/{record}/edit'),
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
