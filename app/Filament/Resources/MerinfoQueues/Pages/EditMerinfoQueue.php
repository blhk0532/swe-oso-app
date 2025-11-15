<?php

namespace App\Filament\Resources\MerinfoQueues\Pages;

use App\Filament\Resources\MerinfoQueues\MerinfoQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMerinfoQueue extends EditRecord
{
    protected static string $resource = MerinfoQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
