<?php

use App\Filament\Resources\UpplysningDatas\Pages\CreateUpplysningData;
use App\Filament\Resources\UpplysningDatas\Pages\EditUpplysningData;
use App\Filament\Resources\UpplysningDatas\Pages\ListUpplysningDatas;
use App\Models\UpplysningData;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('can list upplysning data', function () {
    UpplysningData::query()->create(['personnamn' => 'Alpha']);
    UpplysningData::query()->create(['personnamn' => 'Beta']);
    UpplysningData::query()->create(['personnamn' => 'Gamma']);

    Livewire::test(ListUpplysningDatas::class)
        ->assertSuccessful();
});

test('can render create page for upplysning', function () {
    Livewire::test(CreateUpplysningData::class)
        ->assertSuccessful();
});

test('can create upplysning record', function () {
    $response = Livewire::test(CreateUpplysningData::class)
        ->fillForm([
            'personnamn' => 'Test Person',
            'gatuadress' => 'Exempelgatan 1',
            'postnummer' => '111 22',
            'postort' => 'Stockholm',
        ])
        ->call('create');

    $response->assertHasNoErrors();

    expect(UpplysningData::where('personnamn', 'Test Person')->exists())->toBeTrue();
});

test('can render edit page for upplysning', function () {
    $record = UpplysningData::create([
        'personnamn' => 'Edit Me',
    ]);

    Livewire::test(EditUpplysningData::class, [
        'record' => $record->getRouteKey(),
    ])->assertSuccessful();
});
