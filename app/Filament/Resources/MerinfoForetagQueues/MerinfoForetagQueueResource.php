<?php

namespace App\Filament\Resources\MerinfoForetagQueues;

use App\Filament\Resources\MerinfoForetagQueues\Pages\CreateMerinfoForetagQueue;
use App\Filament\Resources\MerinfoForetagQueues\Pages\EditMerinfoForetagQueue;
use App\Filament\Resources\MerinfoForetagQueues\Pages\ListMerinfoForetagQueues;
use App\Filament\Resources\MerinfoForetagQueues\Tables\MerinfoForetagQueuesTable;
use App\Filament\Resources\MerinfoQueues\Schemas\MerinfoQueueForm;
use App\Models\MerinfoForetagQueue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MerinfoForetagQueueResource extends Resource
{
    protected static ?string $model = MerinfoForetagQueue::class;

    protected static ?string $navigationLabel = 'Merinfo Företag Queue';

    protected static ?string $slug = 'queues/merinfo-foretag-queue';

    protected static ?int $navigationSort = 4;

    protected static string | UnitEnum | null $navigationGroup = 'MERINFO DATABAS';

    public static function form(Schema $schema): Schema
    {
        return MerinfoQueueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfoForetagQueuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerinfoForetagQueues::route('/'),
            'create' => CreateMerinfoForetagQueue::route('/create'),
            'edit' => EditMerinfoForetagQueue::route('/{record}/edit'),
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
