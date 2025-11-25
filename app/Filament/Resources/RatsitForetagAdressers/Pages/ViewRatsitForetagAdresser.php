<?php

namespace App\Filament\Resources\RatsitForetagAdressers\Pages;

use App\Filament\Resources\RatsitForetagAdressers\RatsitForetagAdresserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitForetagAdresser extends ViewRecord
{
    protected static string $resource = RatsitForetagAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
