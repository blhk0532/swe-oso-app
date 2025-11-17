<?php

namespace App\Filament\Resources\HittaForetagDatas\Pages;

use App\Filament\Resources\HittaForetagDatas\HittaForetagDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHittaForetagDatas extends ListRecords
{
    protected static string $resource = HittaForetagDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
