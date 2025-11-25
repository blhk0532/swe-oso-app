<?php

namespace App\Filament\Actions\PostNummerChecks;

use App\Jobs\RunPostNummerChecksJob;
use App\Models\PostNummerCheck;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class RunBothChecksAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('both_checks')
            ->label('Kör Båda Kontroller')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->action(function (PostNummerCheck $record) {
                try {
                    $postNummer = str_replace(' ', '', $record->post_nummer);

                    Log::info("Dispatching postnummer checks job for: {$postNummer}");

                    // Dispatch the job to the queue
                    $job = RunPostNummerChecksJob::dispatch($record);

                    Log::info('Job dispatched successfully', [
                        'post_nummer' => $postNummer,
                        'job_class' => get_class($job),
                        'queue' => 'postnummer-checks',
                    ]);

                    Notification::make()
                        ->title('Kontroller har startats')
                        ->body("Postnummer {$postNummer} kontroller har lagts i kön och körs i bakgrunden.")
                        ->info()
                        ->send();

                } catch (Exception $e) {
                    Log::error('Failed to dispatch postnummer checks job', [
                        'post_nummer' => $record->post_nummer,
                        'error' => $e->getMessage(),
                    ]);

                    Notification::make()
                        ->title('Kunde inte starta kontroller')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function make(?string $name = null): static
    {
        return new static($name);
    }
}
