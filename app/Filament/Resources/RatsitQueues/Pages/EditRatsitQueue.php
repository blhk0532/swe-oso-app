<?php

namespace App\Filament\Resources\RatsitQueues\Pages;

use App\Filament\Resources\RatsitQueues\RatsitQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitQueue extends EditRecord
{
    protected static string $resource = RatsitQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
