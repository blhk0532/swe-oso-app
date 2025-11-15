<?php

namespace App\Filament\Resources\MerinfoQueues\Pages;

use App\Filament\Resources\MerinfoQueues\MerinfoQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMerinfoQueue extends ViewRecord
{
    protected static string $resource = MerinfoQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
