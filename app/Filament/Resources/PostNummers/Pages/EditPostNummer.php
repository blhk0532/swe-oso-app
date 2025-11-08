<?php

namespace App\Filament\Resources\PostNummers\Pages;

use App\Filament\Resources\PostNummers\PostNummerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostNummer extends EditRecord
{
    protected static string $resource = PostNummerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
