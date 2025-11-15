<?php

namespace App\Filament\Resources\PostNummerQueues\Pages;

use App\Filament\Resources\PostNummerQueues\PostNummerQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPostNummerQueue extends EditRecord
{
    protected static string $resource = PostNummerQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
