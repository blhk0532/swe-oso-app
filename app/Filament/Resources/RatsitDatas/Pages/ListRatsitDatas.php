<?php

namespace App\Filament\Resources\RatsitDatas\Pages;

use App\Filament\Resources\RatsitDatas\RatsitDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitDatas extends ListRecords
{
    protected static string $resource = RatsitDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
