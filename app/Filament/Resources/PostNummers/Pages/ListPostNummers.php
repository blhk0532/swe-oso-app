<?php

namespace App\Filament\Resources\PostNummers\Pages;

use App\Filament\Resources\PostNummers\PostNummerResource;
use App\Filament\Widgets\UpdateProgressWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostNummers extends ListRecords
{
    protected static string $resource = PostNummerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UpdateProgressWidget::class,
        ];
    }

    protected function getTablePollingInterval(): ?string
    {
        // Frequently poll so Status and Total Count reflect changes quickly
        return '2s';
    }
}
