<?php

namespace App\Providers;

use App\Jobs\UpdatePostNummersTable;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireUpdateProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Log::info('LivewireUpdateProvider booted successfully');

        // Register global Livewire event listeners
        $this->registerLivewireListeners();
    }

    /**
     * Register Livewire event listeners for all components
     */
    protected function registerLivewireListeners(): void
    {
        // Listen for component mount
        Livewire::listen('component.mount', function ($component) {
            $this->handleLivewireEvent('mount', $component, [
                'component' => get_class($component),
                'id' => $component->getId(),
            ]);
        });

        // Listen for property updates
        Livewire::listen('property.updated', function ($component, $property, $value) {
            $this->handleLivewireEvent('property.updated', $component, [
                'component' => get_class($component),
                'id' => $component->getId(),
                'property' => $property,
                'value' => $value,
            ]);
        });

        // Listen for action calls
        Livewire::listen('action.called', function ($component, $action, $params) {
            $this->handleLivewireEvent('action.called', $component, [
                'component' => get_class($component),
                'id' => $component->getId(),
                'action' => $action,
                'params' => $params,
            ]);
        });

        // Listen for component dehydration (before sending to browser)
        Livewire::listen('component.dehydrate', function ($component) {
            $this->handleLivewireEvent('dehydrate', $component, [
                'component' => get_class($component),
                'id' => $component->getId(),
            ]);
        });

        // Listen for component hydration (when receiving from browser)
        Livewire::listen('component.hydrate', function ($component) {
            $this->handleLivewireEvent('hydrate', $component, [
                'component' => get_class($component),
                'id' => $component->getId(),
            ]);
        });
    }

    /**
     * Handle Livewire events by dispatching update job
     */
    protected function handleLivewireEvent(string $event, $component, array $data = []): void
    {
        try {
            // Only dispatch for certain events to avoid too many jobs
            $importantEvents = ['mount', 'property.updated', 'action.called'];

            if (in_array($event, $importantEvents)) {
                // Dispatch job to update post-nummers table
                UpdatePostNummersTable::dispatch($event, $data, now()->toISOString());

                Log::info("Livewire event '{$event}' triggered post-nummers update", [
                    'component' => $data['component'] ?? 'unknown',
                    'data' => $data,
                ]);
            }
        } catch (Exception $e) {
            Log::error("Failed to handle Livewire event '{$event}': " . $e->getMessage(), [
                'component' => $data['component'] ?? 'unknown',
                'data' => $data,
            ]);
        }
    }
}
