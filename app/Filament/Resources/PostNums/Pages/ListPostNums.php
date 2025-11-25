<?php

namespace App\Filament\Resources\PostNums\Pages;

use App\Filament\Resources\PostNums\PostNumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostNums extends ListRecords
{
    protected static string $resource = PostNumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
