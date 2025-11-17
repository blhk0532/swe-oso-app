<?php

namespace App\Filament\Resources\HittaForetagQueues\Pages;

use App\Filament\Resources\HittaForetagQueues\HittaForetagQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHittaForetagQueues extends ListRecords
{
    protected static string $resource = HittaForetagQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
