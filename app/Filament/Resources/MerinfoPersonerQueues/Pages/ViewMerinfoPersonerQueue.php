<?php

namespace App\Filament\Resources\MerinfoPersonerQueues\Pages;

use App\Filament\Resources\MerinfoPersonerQueues\MerinfoPersonerQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMerinfoPersonerQueue extends ViewRecord
{
    protected static string $resource = MerinfoPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
