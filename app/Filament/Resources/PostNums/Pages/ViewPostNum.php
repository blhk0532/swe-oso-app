<?php

namespace App\Filament\Resources\PostNums\Pages;

use App\Filament\Resources\PostNums\PostNumResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPostNum extends ViewRecord
{
    protected static string $resource = PostNumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
