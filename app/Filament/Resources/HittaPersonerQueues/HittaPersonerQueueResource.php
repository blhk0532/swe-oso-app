<?php

namespace App\Filament\Resources\HittaPersonerQueues;

use App\Filament\Resources\HittaPersonerQueues\Pages\CreateHittaPersonerQueue;
use App\Filament\Resources\HittaPersonerQueues\Pages\EditHittaPersonerQueue;
use App\Filament\Resources\HittaPersonerQueues\Pages\ListHittaPersonerQueues;
use App\Filament\Resources\HittaPersonerQueues\Pages\ViewHittaPersonerQueue;
use App\Filament\Resources\HittaPersonerQueues\Schemas\HittaPersonerQueueForm;
use App\Filament\Resources\HittaPersonerQueues\Schemas\HittaPersonerQueueInfolist;
use App\Filament\Resources\HittaPersonerQueues\Tables\HittaPersonerQueuesTable;
use App\Models\HittaPersonerQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HittaPersonerQueueResource extends Resource
{
    protected static ?string $model = HittaPersonerQueue::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Hitta Personer Queue';

    protected static ?string $modelLabel = 'Hitta Personer Queue';

    protected static ?string $pluralModelLabel = 'Hitta Personer Queues';

    protected static ?string $recordTitleAttribute = 'post_nummer';

    protected static UnitEnum | string | null $navigationGroup = 'HITTA DATABAS';

    public static function form(Schema $schema): Schema
    {
        return HittaPersonerQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HittaPersonerQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HittaPersonerQueuesTable::configure($table);
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
            'index' => ListHittaPersonerQueues::route('/'),
            'create' => CreateHittaPersonerQueue::route('/create'),
            'view' => ViewHittaPersonerQueue::route('/{record}'),
            'edit' => EditHittaPersonerQueue::route('/{record}/edit'),
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
}
