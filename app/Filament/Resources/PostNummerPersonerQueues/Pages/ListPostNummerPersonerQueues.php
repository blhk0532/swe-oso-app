<?php

namespace App\Filament\Resources\PostNummerPersonerQueues\Pages;

use App\Filament\Resources\PostNummerPersonerQueues\PostNummerPersonerQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostNummerPersonerQueues extends ListRecords
{
    protected static string $resource = PostNummerPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
