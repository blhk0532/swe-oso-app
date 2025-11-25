<?php

namespace App\Filament\Resources\RatsitPersonKommuners\Pages;

use App\Filament\Resources\RatsitPersonKommuners\RatsitPersonKommunerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitPersonKommuners extends ListRecords
{
    protected static string $resource = RatsitPersonKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
