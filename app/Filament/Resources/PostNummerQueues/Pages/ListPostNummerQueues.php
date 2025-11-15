<?php

namespace App\Filament\Resources\PostNummerQueues\Pages;

use App\Filament\Resources\PostNummerQueues\PostNummerQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostNummerQueues extends ListRecords
{
    protected static string $resource = PostNummerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
