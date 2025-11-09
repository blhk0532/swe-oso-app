<?php

namespace App\Filament\Resources\MerinfoDatas\Pages;

use App\Filament\Resources\MerinfoDatas\MerinfoDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoDatas extends ListRecords
{
    protected static string $resource = MerinfoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
