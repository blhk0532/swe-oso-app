<?php

use App\Filament\Resources\PostNummers\Pages\CreatePostNummer;
use App\Filament\Resources\PostNummers\Pages\EditPostNummer;
use App\Filament\Resources\PostNummers\Pages\ListPostNummers;
use App\Models\PostNummer;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('can list post nummers', function () {
    // Create some test records
    PostNummer::factory()->count(3)->create();

    Livewire::test(ListPostNummers::class)
        ->assertSuccessful();
});

test('can render create page', function () {
    Livewire::test(CreatePostNummer::class)
        ->assertSuccessful();
});

test('can create post nummer', function () {
    // Delete any existing record first to ensure uniqueness
    PostNummer::where('post_nummer', '00100')->delete();

    $response = Livewire::test(CreatePostNummer::class)
        ->fillForm([
            'post_nummer' => '00100',
            'post_ort' => 'Stockholm',
            'total_count' => 5,
            'is_pending' => false,
            'is_complete' => true,
            'is_active' => true,
        ])
        ->call('create');

    $response->assertHasNoErrors();
});

test('can render edit page', function () {
    $postNummer = PostNummer::first();

    Livewire::test(EditPostNummer::class, [
        'record' => $postNummer->getRouteKey(),
    ])
        ->assertSuccessful();
});

test('can update post nummer', function () {
    $postNummer = PostNummer::first();

    Livewire::test(EditPostNummer::class, [
        'record' => $postNummer->getRouteKey(),
    ])
        ->fillForm([
            'post_ort' => 'Göteborg',
            'total_count' => 10,
            'status' => 'complete',
        ])
        ->call('save')
        ->assertHasNoErrors();

    $postNummer->refresh();

    expect($postNummer->post_ort)->toBe('Göteborg')
        ->and($postNummer->total_count)->toBe(10);
});

test('validates post nummer is required', function () {
    Livewire::test(CreatePostNummer::class)
        ->fillForm([
            'post_nummer' => null,
        ])
        ->call('create')
        ->assertHasErrors(['data.post_nummer']);
});

test('validates post nummer is unique', function () {
    $existing = PostNummer::first();

    Livewire::test(CreatePostNummer::class)
        ->fillForm([
            'post_nummer' => $existing->post_nummer,
        ])
        ->call('create')
        ->assertHasErrors(['data.post_nummer']);
});
