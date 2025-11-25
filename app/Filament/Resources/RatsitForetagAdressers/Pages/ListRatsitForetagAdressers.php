<?php

namespace App\Filament\Resources\RatsitForetagAdressers\Pages;

use App\Filament\Resources\RatsitForetagAdressers\RatsitForetagAdresserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitForetagAdressers extends ListRecords
{
    protected static string $resource = RatsitForetagAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
