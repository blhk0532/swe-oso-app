<?php

namespace App\Filament\Resources\RatsitForetagAdressers\Pages;

use App\Filament\Resources\RatsitForetagAdressers\RatsitForetagAdresserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitForetagAdresser extends EditRecord
{
    protected static string $resource = RatsitForetagAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
