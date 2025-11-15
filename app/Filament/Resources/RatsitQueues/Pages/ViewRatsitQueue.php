<?php

namespace App\Filament\Resources\RatsitQueues\Pages;

use App\Filament\Resources\RatsitQueues\RatsitQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitQueue extends ViewRecord
{
    protected static string $resource = RatsitQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
