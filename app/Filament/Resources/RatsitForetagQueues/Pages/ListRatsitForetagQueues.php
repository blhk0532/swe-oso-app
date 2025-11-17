<?php

namespace App\Filament\Resources\RatsitForetagQueues\Pages;

use App\Filament\Resources\RatsitForetagQueues\RatsitForetagQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitForetagQueues extends ListRecords
{
    protected static string $resource = RatsitForetagQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
