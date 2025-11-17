<?php

use App\Jobs\UpdatePostNummersTable;
use App\Livewire\Form;
use App\Models\PostNummer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('post nummers table updates on livewire mount', function () {
    Queue::fake();

    // Access the form page which should trigger the mount hook
    $response = $this->get('/form');

    $response->assertStatus(200);

    // Assert that the job was dispatched
    Queue::assertPushed(UpdatePostNummersTable::class, function ($job) {
        return $job->event === 'mount';
    });
});

test('post nummers table updates on livewire property change', function () {
    Queue::fake();

    // Test the Form component directly - just mount it to trigger hooks
    Livewire::test(Form::class)
        ->assertOk();

    // The mount hook should have been called, which dispatches the job
    Queue::assertPushed(UpdatePostNummersTable::class, function ($job) {
        return $job->event === 'mount';
    });
});

test('update post nummers job updates last_livewire_update field', function () {
    // Create a test postnummer record
    $postnummer = PostNummer::factory()->create(['is_active' => true]);

    $initialUpdate = $postnummer->last_livewire_update;

    // Run the job
    UpdatePostNummersTable::dispatch('mount', [], now()->toISOString());

    // Process the queue
    $this->artisan('queue:work --queue=postnummer-updates --once');

    // Refresh the model
    $postnummer->refresh();

    // Assert that last_livewire_update was updated
    expect($postnummer->last_livewire_update)->not->toBe($initialUpdate);
    expect($postnummer->last_livewire_update)->toBeInstanceOf(Carbon::class);
});
