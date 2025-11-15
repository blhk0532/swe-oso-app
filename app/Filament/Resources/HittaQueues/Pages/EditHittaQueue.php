<?php

namespace App\Filament\Resources\HittaQueues\Pages;

use App\Filament\Resources\HittaQueues\HittaQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHittaQueue extends EditRecord
{
    protected static string $resource = HittaQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
