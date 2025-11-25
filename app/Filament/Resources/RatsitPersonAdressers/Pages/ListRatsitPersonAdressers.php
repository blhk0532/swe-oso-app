<?php

namespace App\Filament\Resources\RatsitPersonAdressers\Pages;

use App\Filament\Resources\RatsitPersonAdressers\RatsitPersonAdresserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitPersonAdressers extends ListRecords
{
    protected static string $resource = RatsitPersonAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
