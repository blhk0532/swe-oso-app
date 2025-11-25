<?php

namespace App\Filament\Resources\RatsitPersonAdressers\Pages;

use App\Filament\Resources\RatsitPersonAdressers\RatsitPersonAdresserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitPersonAdresser extends ViewRecord
{
    protected static string $resource = RatsitPersonAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
