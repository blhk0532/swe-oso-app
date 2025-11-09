<?php

use App\Filament\Resources\MerinfoDatas\Pages\CreateMerinfoData;
use App\Filament\Resources\MerinfoDatas\Pages\EditMerinfoData;
use App\Filament\Resources\MerinfoDatas\Pages\ListMerinfoDatas;
use App\Models\MerinfoData;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('can list merinfo data', function () {
    MerinfoData::query()->create(['personnamn' => 'Alpha']);
    MerinfoData::query()->create(['personnamn' => 'Beta']);
    MerinfoData::query()->create(['personnamn' => 'Gamma']);

    Livewire::test(ListMerinfoDatas::class)
        ->assertSuccessful();
});

test('can render create page for merinfo', function () {
    Livewire::test(CreateMerinfoData::class)
        ->assertSuccessful();
});

test('can create merinfo record', function () {
    $response = Livewire::test(CreateMerinfoData::class)
        ->fillForm([
            'personnamn' => 'Test Person',
            'gatuadress' => 'Exempelgatan 1',
            'postnummer' => '111 22',
            'postort' => 'Stockholm',
        ])
        ->call('create');

    $response->assertHasNoErrors();

    expect(MerinfoData::where('personnamn', 'Test Person')->exists())->toBeTrue();
});

test('can render edit page for merinfo', function () {
    $record = MerinfoData::create([
        'personnamn' => 'Edit Me',
    ]);

    Livewire::test(EditMerinfoData::class, [
        'record' => $record->getRouteKey(),
    ])->assertSuccessful();
});
