<?php

namespace App\Filament\Resources\RatsitForetagQueues;

use App\Filament\Resources\RatsitForetagQueues\Pages\CreateRatsitForetagQueue;
use App\Filament\Resources\RatsitForetagQueues\Pages\EditRatsitForetagQueue;
use App\Filament\Resources\RatsitForetagQueues\Pages\ListRatsitForetagQueues;
use App\Filament\Resources\RatsitForetagQueues\Tables\RatsitForetagQueuesTable;
use App\Filament\Resources\RatsitQueues\Schemas\RatsitQueueForm;
use App\Models\RatsitForetagQueue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RatsitForetagQueueResource extends Resource
{
    protected static ?string $model = RatsitForetagQueue::class;

    protected static ?string $navigationLabel = 'Ratsit Företag Queue';

    protected static UnitEnum | string | null $navigationGroup = 'RATSIT DATABAS';

    protected static ?string $slug = 'queues/ratsit-foretag-queue';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return RatsitQueueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitForetagQueuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatsitForetagQueues::route('/'),
            'create' => CreateRatsitForetagQueue::route('/create'),
            'edit' => EditRatsitForetagQueue::route('/{record}/edit'),
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
