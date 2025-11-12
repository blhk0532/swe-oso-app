<?php

namespace App\Filament\Resources\PostNummerQues\Pages;

use App\Filament\Resources\PostNummerQues\PostNummerQueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostNummerQues extends ListRecords
{
    protected static string $resource = PostNummerQueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
