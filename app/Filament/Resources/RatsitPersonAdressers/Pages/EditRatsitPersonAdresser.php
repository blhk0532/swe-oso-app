<?php

namespace App\Filament\Resources\RatsitPersonAdressers\Pages;

use App\Filament\Resources\RatsitPersonAdressers\RatsitPersonAdresserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitPersonAdresser extends EditRecord
{
    protected static string $resource = RatsitPersonAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
