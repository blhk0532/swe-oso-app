<?php

namespace App\Filament\Resources\MerinfoForetagDatas\Pages;

use App\Filament\Resources\MerinfoForetagDatas\MerinfoForetagDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoForetagDatas extends ListRecords
{
    protected static string $resource = MerinfoForetagDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
