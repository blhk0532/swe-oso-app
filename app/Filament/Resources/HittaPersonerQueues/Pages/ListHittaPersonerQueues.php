<?php

namespace App\Filament\Resources\HittaPersonerQueues\Pages;

use App\Filament\Resources\HittaPersonerQueues\HittaPersonerQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHittaPersonerQueues extends ListRecords
{
    protected static string $resource = HittaPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
