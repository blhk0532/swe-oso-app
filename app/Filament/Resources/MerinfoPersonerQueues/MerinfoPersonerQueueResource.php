<?php

namespace App\Filament\Resources\MerinfoPersonerQueues;

use App\Filament\Resources\MerinfoPersonerQueues\Pages\CreateMerinfoPersonerQueue;
use App\Filament\Resources\MerinfoPersonerQueues\Pages\EditMerinfoPersonerQueue;
use App\Filament\Resources\MerinfoPersonerQueues\Pages\ListMerinfoPersonerQueues;
use App\Filament\Resources\MerinfoPersonerQueues\Pages\ViewMerinfoPersonerQueue;
use App\Filament\Resources\MerinfoPersonerQueues\Schemas\MerinfoPersonerQueueForm;
use App\Filament\Resources\MerinfoPersonerQueues\Schemas\MerinfoPersonerQueueInfolist;
use App\Filament\Resources\MerinfoPersonerQueues\Tables\MerinfoPersonerQueuesTable;
use App\Models\MerinfoPersonerQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MerinfoPersonerQueueResource extends Resource
{
    protected static ?string $model = MerinfoPersonerQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Merinfo Personer Queue';

    protected static ?string $modelLabel = 'Merinfo Personer Queue';

    protected static ?string $pluralModelLabel = 'Merinfo Personer Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static ?int $navigationSort = 2;

    protected static UnitEnum | string | null $navigationGroup = 'MERINFO DATABAS';

    public static function form(Schema $schema): Schema
    {
        return MerinfoPersonerQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerinfoPersonerQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfoPersonerQueuesTable::configure($table);
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
            'index' => ListMerinfoPersonerQueues::route('/'),
            'create' => CreateMerinfoPersonerQueue::route('/create'),
            'view' => ViewMerinfoPersonerQueue::route('/{record}'),
            'edit' => EditMerinfoPersonerQueue::route('/{record}/edit'),
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
