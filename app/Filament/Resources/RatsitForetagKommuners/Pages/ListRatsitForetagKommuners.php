<?php

namespace App\Filament\Resources\RatsitForetagKommuners\Pages;

use App\Filament\Resources\RatsitForetagKommuners\RatsitForetagKommunerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitForetagKommuners extends ListRecords
{
    protected static string $resource = RatsitForetagKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
