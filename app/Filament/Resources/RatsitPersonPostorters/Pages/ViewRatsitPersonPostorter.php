<?php

namespace App\Filament\Resources\RatsitPersonPostorters\Pages;

use App\Filament\Resources\RatsitPersonPostorters\RatsitPersonPostorterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitPersonPostorter extends ViewRecord
{
    protected static string $resource = RatsitPersonPostorterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
