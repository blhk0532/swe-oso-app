<?php

namespace App\Filament\Resources\PersonerData\Pages;

use App\Filament\Resources\PersonerData\PersonerDataResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPersonerData extends EditRecord
{
    protected static string $resource = PersonerDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
