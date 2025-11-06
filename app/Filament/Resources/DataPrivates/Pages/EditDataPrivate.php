<?php

namespace App\Filament\Resources\DataPrivates\Pages;

use App\Filament\Resources\DataPrivates\DataPrivateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDataPrivate extends EditRecord
{
    protected static string $resource = DataPrivateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
