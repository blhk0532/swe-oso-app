<?php

namespace App\Filament\Resources\PostNummerForetagQueues\Pages;

use App\Filament\Resources\PostNummerForetagQueues\PostNummerForetagQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostNummerForetagQueues extends ListRecords
{
    protected static string $resource = PostNummerForetagQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
