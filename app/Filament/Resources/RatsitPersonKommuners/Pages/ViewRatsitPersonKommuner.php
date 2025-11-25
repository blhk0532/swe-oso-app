<?php

namespace App\Filament\Resources\RatsitPersonKommuners\Pages;

use App\Filament\Resources\RatsitPersonKommuners\RatsitPersonKommunerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitPersonKommuner extends ViewRecord
{
    protected static string $resource = RatsitPersonKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
