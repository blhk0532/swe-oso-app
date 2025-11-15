<?php

namespace App\Filament\Resources\PostNummerPersonerQueues\Pages;

use App\Filament\Resources\PostNummerPersonerQueues\PostNummerPersonerQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPostNummerPersonerQueue extends EditRecord
{
    protected static string $resource = PostNummerPersonerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
