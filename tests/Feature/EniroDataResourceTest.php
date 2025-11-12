<?php

use App\Filament\Resources\EniroDatas\Pages\CreateEniroData;
use App\Filament\Resources\EniroDatas\Pages\EditEniroData;
use App\Filament\Resources\EniroDatas\Pages\ListEniroDatas;
use App\Models\EniroData;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('can list eniro data', function () {
    EniroData::query()->create(['personnamn' => 'Alpha']);
    EniroData::query()->create(['personnamn' => 'Beta']);
    EniroData::query()->create(['personnamn' => 'Gamma']);

    Livewire::test(ListEniroDatas::class)
        ->assertSuccessful();
});

test('can render create page for eniro', function () {
    Livewire::test(CreateEniroData::class)
        ->assertSuccessful();
});

test('can create eniro record', function () {
    $response = Livewire::test(CreateEniroData::class)
        ->fillForm([
            'personnamn' => 'Test Person',
            'gatuadress' => 'Exempelgatan 1',
            'postnummer' => '111 22',
            'postort' => 'Stockholm',
        ])
        ->call('create');

    $response->assertHasNoErrors();

    expect(EniroData::where('personnamn', 'Test Person')->exists())->toBeTrue();
});

test('can render edit page for eniro', function () {
    $record = EniroData::create([
        'personnamn' => 'Edit Me',
    ]);

    Livewire::test(EditEniroData::class, [
        'record' => $record->getRouteKey(),
    ])->assertSuccessful();
});
