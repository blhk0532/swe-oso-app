<?php

namespace App\Filament\Resources\MerinfoDatas\Pages;

use App\Filament\Resources\MerinfoDatas\MerinfoDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerinfoDatas extends ListRecords
{
    protected static string $resource = MerinfoDataResource::class;

    // Hide the default header title for this resource (removes the <h1 class="fi-header-heading">)
    protected ?string $heading = '';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
