<?php

namespace App\Filament\Resources\HittaPersonerQueues\Pages;

use App\Filament\Resources\HittaPersonerQueues\HittaPersonerQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHittaPersonerQueue extends EditRecord
{
    protected static string $resource = HittaPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
