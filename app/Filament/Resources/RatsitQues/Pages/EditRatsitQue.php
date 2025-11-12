<?php

namespace App\Filament\Resources\RatsitQues\Pages;

use App\Filament\Resources\RatsitQues\RatsitQueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitQue extends EditRecord
{
    protected static string $resource = RatsitQueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
