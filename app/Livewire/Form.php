<?php

namespace App\Livewire;

use App\Jobs\UpdatePostNummersTable;
use Exception;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
/**
 * @property-read Schema $form
 */
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public $data = [];

    public function mount(): void
    {
        if (! app()->environment('local')) {
            abort(404);
        }

        $this->form->fill();

        // Update post-nummers table on component mount
        $this->updatePostNummersTable('mount');
    }

    public function updating($property, $value): void
    {
        // Update post-nummers table when any property is being updated
        Log::info("Livewire Form updating: {$property}");
        $this->updatePostNummersTable('updating', ['property' => $property, 'value' => $value]);
    }

    public function updated($property, $value): void
    {
        // Update post-nummers table after any property is updated
        Log::info("Livewire Form updated: {$property}");
        $this->updatePostNummersTable('updated', ['property' => $property, 'value' => $value]);
    }

    protected function updatePostNummersTable(string $event, array $data = []): void
    {
        try {
            // Dispatch job to update post-nummers table
            UpdatePostNummersTable::dispatch($event, $data, now()->toISOString());

            Log::info("Dispatched UpdatePostNummersTable job for event: {$event}");
        } catch (Exception $e) {
            Log::error('Failed to dispatch UpdatePostNummersTable job: ' . $e->getMessage());
        }
    }

    /** @return \Filament\Schemas\Components\Component[] */
    protected function getFormSchema(): array
    {
        return [
            Builder::make('test')
                ->blocks([
                    Block::make('one')
                        ->schema([
                            TextInput::make('one'),
                        ]),
                    Block::make('two')
                        ->schema([
                            TextInput::make('two'),
                        ]),
                ]),
        ];
    }

    public function submit(): never
    {
        dd($this->form->getState());
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function render(): View
    {
        return view('livewire.form');
    }
}
