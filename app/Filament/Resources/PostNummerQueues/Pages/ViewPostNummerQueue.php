<?php

namespace App\Filament\Resources\PostNummerQueues\Pages;

use App\Filament\Resources\PostNummerQueues\PostNummerQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPostNummerQueue extends ViewRecord
{
    protected static string $resource = PostNummerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
