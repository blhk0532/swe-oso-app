<?php

namespace App\Filament\Actions\PostNummerChecks;

use App\Models\PostNummerCheck;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class RunRatsitCheckAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('ratsit_check')
            ->label('Kör Ratsit Kontroll')
            ->icon('heroicon-o-magnifying-glass')
            ->color('warning')
            ->action(function (PostNummerCheck $record) {
                try {
                    $postNummer = str_replace(' ', '', $record->post_nummer);

                    Log::info("Running Ratsit check for postnummer: {$postNummer}");

                    // Run Node.js script
                    $scriptPath = base_path('scripts/ratsit_check_counts.mjs');
                    $command = "node {$scriptPath} \"{$postNummer}\"";

                    Log::info("Executing command: {$command}");

                    $output = shell_exec($command);

                    Log::info('Script output: ' . $output);

                    if ($output && trim($output) !== '') {
                        // Parse the output
                        $lines = explode("\n", trim($output));
                        $personer = 0;
                        $foretag = 0;

                        foreach ($lines as $line) {
                            if (preg_match('/"personer":\s*(\d+)"/', $line, $matches)) {
                                $personer = (int) $matches[1];
                            }
                            if (preg_match('/"foretag":\s*(\d+)"/', $line, $matches)) {
                                $foretag = (int) $matches[1];
                            }
                        }

                        // Update the record
                        $record->update([
                            'ratsit_personer_total' => $personer,
                            'ratsit_foretag_total' => $foretag,
                        ]);

                        Notification::make()
                            ->title('Ratsit kontroll slutförd')
                            ->body("Postnummer {$postNummer}: {$personer} personer, {$foretag} företag")
                            ->success()
                            ->send();
                    } else {
                        Log::error("No output from Ratsit script for postnummer: {$postNummer}");
                        Notification::make()
                            ->title('Fel vid Ratsit kontroll')
                            ->body('Kunde inte hämta data från Ratsit')
                            ->danger()
                            ->send();
                    }
                } catch (Exception $e) {
                    Log::error('Ratsit check failed: ' . $e->getMessage());

                    Notification::make()
                        ->title('Fel vid Ratsit kontroll')
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
