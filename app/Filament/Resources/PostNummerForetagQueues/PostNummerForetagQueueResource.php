<?php

namespace App\Filament\Resources\PostNummerForetagQueues;

use App\Filament\Resources\PostNummerForetagQueues\Pages\CreatePostNummerForetagQueue;
use App\Filament\Resources\PostNummerForetagQueues\Pages\EditPostNummerForetagQueue;
use App\Filament\Resources\PostNummerForetagQueues\Pages\ListPostNummerForetagQueues;
use App\Filament\Resources\PostNummerForetagQueues\Tables\PostNummerForetagQueuesTable;
use App\Filament\Resources\PostNummerQueues\Schemas\PostNummerQueueForm;
use App\Models\PostNummerForetagQueue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PostNummerForetagQueueResource extends Resource
{
    protected static ?string $model = PostNummerForetagQueue::class;

    protected static ?string $navigationLabel = 'Postnummer Företag Queue';

    protected static UnitEnum | string | null $navigationGroup = 'POST NUMMER';

    protected static ?string $slug = 'queues/postnummer-foretag-queue';

    public static function form(Schema $schema): Schema
    {
        return PostNummerQueueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostNummerForetagQueuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostNummerForetagQueues::route('/'),
            'create' => CreatePostNummerForetagQueue::route('/create'),
            'edit' => EditPostNummerForetagQueue::route('/{record}/edit'),
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
