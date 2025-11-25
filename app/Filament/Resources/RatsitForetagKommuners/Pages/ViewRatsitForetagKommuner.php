<?php

namespace App\Filament\Resources\RatsitForetagKommuners\Pages;

use App\Filament\Resources\RatsitForetagKommuners\RatsitForetagKommunerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitForetagKommuner extends ViewRecord
{
    protected static string $resource = RatsitForetagKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
