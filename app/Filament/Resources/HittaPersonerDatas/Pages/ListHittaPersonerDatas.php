<?php

namespace App\Filament\Resources\HittaPersonerDatas\Pages;

use App\Filament\Resources\HittaPersonerDatas\HittaPersonerDataResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHittaPersonerDatas extends ListRecords
{
    protected static string $resource = HittaPersonerDataResource::class;

    protected function getActions(): array
    {
        return [CreateAction::make()];
    }
}
