<?php

namespace App\Filament\Resources\MerinfoPersonerDatas\Pages;

use App\Filament\Resources\MerinfoPersonerDatas\MerinfoPersonerDataResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoPersonerDatas extends ListRecords
{
    protected static string $resource = MerinfoPersonerDataResource::class;

    protected function getActions(): array
    {
        return [CreateAction::make()];
    }
}
