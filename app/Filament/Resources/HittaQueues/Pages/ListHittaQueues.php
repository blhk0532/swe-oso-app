<?php

namespace App\Filament\Resources\HittaQueues\Pages;

use App\Filament\Resources\HittaQueues\HittaQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHittaQueues extends ListRecords
{
    protected static string $resource = HittaQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
