<?php

namespace App\Filament\Resources\MerinfoQueues\Pages;

use App\Filament\Resources\MerinfoQueues\MerinfoQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoQueues extends ListRecords
{
    protected static string $resource = MerinfoQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
