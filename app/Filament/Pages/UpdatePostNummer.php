<?php

namespace App\Filament\Pages;

use App\Jobs\UpdatePostNummerFromSweden;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

class UpdatePostNummer extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected string $view = 'filament.pages.update-post-nummer';

    protected static ?string $navigationLabel = 'Update Post Nummer';

    protected static ?string $title = 'Update Post Nummer from Sweden';

    protected static ?int $navigationSort = 100;

    protected static bool $shouldRegisterNavigation = false; // Hide from navigation

    public ?array $progress = null;

    public function mount(): void
    {
        $this->loadProgress();
    }

    #[On('refresh-progress')]
    public function loadProgress(): void
    {
        $this->progress = Cache::get('update_post_nummer_progress');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startUpdate')
                ->label('Start Update')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Start Update Process')
                ->modalDescription('This will update all post_nummer records with data from the sweden table. The process will run in the background.')
                ->modalSubmitActionLabel('Start')
                ->action(function () {
                    // Check if already running
                    $progress = Cache::get('update_post_nummer_progress');
                    if ($progress && $progress['status'] === 'running') {
                        Notification::make()
                            ->title('Already Running')
                            ->body('An update is already in progress.')
                            ->warning()
                            ->send();

                        return;
                    }

                    // Clear previous progress
                    Cache::forget('update_post_nummer_progress');

                    // Dispatch the job
                    UpdatePostNummerFromSweden::dispatch();

                    Notification::make()
                        ->title('Update Started')
                        ->body('The update process has been queued and will start shortly.')
                        ->success()
                        ->send();

                    $this->loadProgress();
                })
                ->disabled(fn () => $this->progress && $this->progress['status'] === 'running'),

            Action::make('clearProgress')
                ->label('Clear Progress')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function () {
                    Cache::forget('update_post_nummer_progress');
                    $this->progress = null;

                    Notification::make()
                        ->title('Progress Cleared')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->progress !== null),
        ];
    }
}
