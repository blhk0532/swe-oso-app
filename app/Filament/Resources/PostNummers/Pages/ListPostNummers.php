<?php

namespace App\Filament\Resources\PostNummers\Pages;

use App\Filament\Resources\PostNummers\PostNummerResource;
use App\Filament\Widgets\UpdateProgressWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListPostNummers extends ListRecords
{
    protected static string $resource = PostNummerResource::class;

    protected $listeners = [
        // Listen to Laravel Echo broadcast from PostNummerStatusUpdated event
        'echo:postnummer.status,PostNummerStatusUpdated' => 'onPostnummerUpdated',
    ];

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
        // WebSocket-driven updates via Echo; no polling needed
        return null;
    }

    public function getFooter(): ?View
    {
        return view('components.postnummer-echo-listener');
    }

    public function onPostnummerUpdated(array $payload = []): void
    {
        // Re-render the page so the Filament table fetches new totals without a full refresh
        $this->dispatch('$refresh');
    }
}
