<?php

namespace App\Filament\Resources\RatsitQueues\Pages;

use App\Filament\Resources\RatsitQueues\RatsitQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitQueues extends ListRecords
{
    protected static string $resource = RatsitQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
