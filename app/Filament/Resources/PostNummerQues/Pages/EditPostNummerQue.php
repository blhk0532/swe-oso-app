<?php

namespace App\Filament\Resources\PostNummerQues\Pages;

use App\Filament\Resources\PostNummerQues\PostNummerQueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostNummerQue extends EditRecord
{
    protected static string $resource = PostNummerQueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
