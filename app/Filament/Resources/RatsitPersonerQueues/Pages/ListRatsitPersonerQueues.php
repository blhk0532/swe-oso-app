<?php

namespace App\Filament\Resources\RatsitPersonerQueues\Pages;

use App\Filament\Resources\RatsitPersonerQueues\RatsitPersonerQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitPersonerQueues extends ListRecords
{
    protected static string $resource = RatsitPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
