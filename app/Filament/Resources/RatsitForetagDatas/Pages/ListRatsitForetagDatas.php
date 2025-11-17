<?php

namespace App\Filament\Resources\RatsitForetagDatas\Pages;

use App\Filament\Resources\RatsitForetagDatas\RatsitForetagDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitForetagDatas extends ListRecords
{
    protected static string $resource = RatsitForetagDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
