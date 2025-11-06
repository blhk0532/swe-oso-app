<?php

namespace App\Filament\Resources\DataPrivates\Pages;

use App\Filament\Exports\DataPrivateExporter;
use App\Filament\Imports\DataPrivateImporter;
use App\Filament\Resources\DataPrivates\DataPrivateResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListDataPrivates extends ListRecords
{
    protected static string $resource = DataPrivateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(DataPrivateImporter::class),
            ExportAction::make()
                ->exporter(DataPrivateExporter::class),
            CreateAction::make(),
        ];
    }
}
