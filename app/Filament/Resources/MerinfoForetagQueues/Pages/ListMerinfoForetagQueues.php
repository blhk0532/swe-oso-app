<?php

namespace App\Filament\Resources\MerinfoForetagQueues\Pages;

use App\Filament\Resources\MerinfoForetagQueues\MerinfoForetagQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoForetagQueues extends ListRecords
{
    protected static string $resource = MerinfoForetagQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
