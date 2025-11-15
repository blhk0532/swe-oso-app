<?php

namespace App\Filament\Resources\HittaPersonerQueues\Pages;

use App\Filament\Resources\HittaPersonerQueues\HittaPersonerQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHittaPersonerQueue extends ViewRecord
{
    protected static string $resource = HittaPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
