<?php

namespace App\Filament\Resources\HittaSes\Pages;

use App\Filament\Resources\HittaSes\HittaSeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHittaSes extends ListRecords
{
    protected static string $resource = HittaSeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
