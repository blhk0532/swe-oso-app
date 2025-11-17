<?php

declare(strict_types=1);

use App\Filament\Resources\MerinfoPersonerDatas\Pages\ListMerinfoPersonerDatas;
use App\Models\MerinfoPersonerData;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can render merinfo personer data list page', function () {
    Livewire::test(ListMerinfoPersonerDatas::class)
        ->assertSuccessful();
});

test('can list merinfo personer data records', function () {
    MerinfoPersonerData::query()->insert([
        [
            'personnamn' => 'Merinfo Person A',
            'alder' => '25',
            'gatuadress' => 'Mer Address A',
            'postnummer' => '333 33',
            'postort' => 'Ort C',
            'telefon' => json_encode([]),
            'is_active' => true,
            'is_telefon' => false,
            'is_ratsit' => false,
            'is_hus' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'personnamn' => 'Merinfo Person B',
            'alder' => '40',
            'gatuadress' => 'Mer Address B',
            'postnummer' => '444 44',
            'postort' => 'Ort D',
            'telefon' => json_encode(['070-1112233']),
            'is_active' => true,
            'is_telefon' => true,
            'is_ratsit' => false,
            'is_hus' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $models = MerinfoPersonerData::all();

    Livewire::test(ListMerinfoPersonerDatas::class)
        ->assertCanSeeTableRecords($models);
});
