<?php

namespace App\Filament\Resources\RatsitForetagPostorters\Pages;

use App\Filament\Resources\RatsitForetagPostorters\RatsitForetagPostorterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitForetagPostorters extends ListRecords
{
    protected static string $resource = RatsitForetagPostorterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
