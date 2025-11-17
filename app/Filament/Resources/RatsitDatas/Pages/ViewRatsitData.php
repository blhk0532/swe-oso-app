<?php

namespace App\Filament\Resources\RatsitDatas\Pages;

use App\Filament\Resources\RatsitDatas\RatsitDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRatsitData extends ViewRecord
{
    protected static string $resource = RatsitDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
