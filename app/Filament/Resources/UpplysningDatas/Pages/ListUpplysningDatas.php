<?php

namespace App\Filament\Resources\UpplysningDatas\Pages;

use App\Filament\Resources\UpplysningDatas\UpplysningDatasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUpplysningDatas extends ListRecords
{
    protected static string $resource = UpplysningDatasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
