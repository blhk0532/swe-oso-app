<?php

namespace App\Filament\Resources\MerinfoPersonerQueues\Pages;

use App\Filament\Resources\MerinfoPersonerQueues\MerinfoPersonerQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoPersonerQueues extends ListRecords
{
    protected static string $resource = MerinfoPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
