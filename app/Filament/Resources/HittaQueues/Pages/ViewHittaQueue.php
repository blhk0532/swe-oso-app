<?php

namespace App\Filament\Resources\HittaQueues\Pages;

use App\Filament\Resources\HittaQueues\HittaQueueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHittaQueue extends ViewRecord
{
    protected static string $resource = HittaQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
