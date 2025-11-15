<?php

namespace App\Filament\Resources\PostNummerPersonerQueues\Pages;

use App\Filament\Resources\PostNummerPersonerQueues\PostNummerPersonerQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPostNummerPersonerQueue extends ViewRecord
{
    protected static string $resource = PostNummerPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
