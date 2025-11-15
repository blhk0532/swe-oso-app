<?php

namespace App\Filament\Resources\RatsitPersonerQueues\Pages;

use App\Filament\Resources\RatsitPersonerQueues\RatsitPersonerQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitPersonerQueue extends EditRecord
{
    protected static string $resource = RatsitPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
