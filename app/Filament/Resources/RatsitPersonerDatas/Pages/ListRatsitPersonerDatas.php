<?php

namespace App\Filament\Resources\RatsitPersonerDatas\Pages;

use App\Filament\Resources\RatsitPersonerDatas\RatsitPersonerDataResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitPersonerDatas extends ListRecords
{
    protected static string $resource = RatsitPersonerDataResource::class;

    protected function getActions(): array
    {
        return [CreateAction::make()];
    }
}
