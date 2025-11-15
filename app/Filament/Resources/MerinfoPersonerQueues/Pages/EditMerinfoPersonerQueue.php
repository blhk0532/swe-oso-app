<?php

namespace App\Filament\Resources\MerinfoPersonerQueues\Pages;

use App\Filament\Resources\MerinfoPersonerQueues\MerinfoPersonerQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMerinfoPersonerQueue extends EditRecord
{
    protected static string $resource = MerinfoPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
