<?php

namespace App\Filament\Resources\RatsitPersonPostorters\Pages;

use App\Filament\Resources\RatsitPersonPostorters\RatsitPersonPostorterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitPersonPostorters extends ListRecords
{
    protected static string $resource = RatsitPersonPostorterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
