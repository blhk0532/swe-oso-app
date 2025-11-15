<?php

namespace App\Filament\Resources\RatsitPersonerQueues\Pages;

use App\Filament\Resources\RatsitPersonerQueues\RatsitPersonerQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitPersonerQueue extends ViewRecord
{
    protected static string $resource = RatsitPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
