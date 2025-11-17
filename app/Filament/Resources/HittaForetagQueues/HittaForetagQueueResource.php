<?php

namespace App\Filament\Resources\HittaForetagQueues;

use App\Filament\Resources\HittaForetagQueues\Pages\CreateHittaForetagQueue;
use App\Filament\Resources\HittaForetagQueues\Pages\EditHittaForetagQueue;
use App\Filament\Resources\HittaForetagQueues\Pages\ListHittaForetagQueues;
use App\Filament\Resources\HittaForetagQueues\Tables\HittaForetagQueuesTable;
use App\Filament\Resources\HittaQueues\Schemas\HittaQueueForm;
use App\Models\HittaForetagQueue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HittaForetagQueueResource extends Resource
{
    protected static ?string $model = HittaForetagQueue::class;

    protected static ?string $navigationLabel = 'Hitta Företag Queue';

    protected static ?string $modelLabel = 'Hitta Företag Queue';

    protected static ?string $pluralModelLabel = 'Hitta Företag Queue';

    protected static UnitEnum | string | null $navigationGroup = 'HITTA DATABAS';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'queues/hitta-foretag-queue';

    public static function form(Schema $schema): Schema
    {
        return HittaQueueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HittaForetagQueuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHittaForetagQueues::route('/'),
            'create' => CreateHittaForetagQueue::route('/create'),
            'edit' => EditHittaForetagQueue::route('/{record}/edit'),
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
