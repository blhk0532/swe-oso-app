<?php

namespace App\Filament\Resources\RatsitQues\Pages;

use App\Filament\Resources\RatsitQues\RatsitQueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRatsitQues extends ListRecords
{
    protected static string $resource = RatsitQueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
