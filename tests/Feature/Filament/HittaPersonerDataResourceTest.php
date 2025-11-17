<?php

declare(strict_types=1);

use App\Filament\Resources\HittaPersonerDatas\Pages\ListHittaPersonerDatas;
use App\Models\HittaPersonerData;
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

test('can render hitta personer data list page', function () {
    Livewire::test(ListHittaPersonerDatas::class)
        ->assertSuccessful();
});

test('can list hitta personer data records', function () {
    HittaPersonerData::query()->insert([
        [
            'personnamn' => 'Test Person A',
            'alder' => '30',
            'gatuadress' => 'Address A',
            'postnummer' => '111 11',
            'postort' => 'Ort A',
            'telefon' => json_encode([]),
            'is_active' => true,
            'is_telefon' => false,
            'is_ratsit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'personnamn' => 'Test Person B',
            'alder' => '40',
            'gatuadress' => 'Address B',
            'postnummer' => '222 22',
            'postort' => 'Ort B',
            'telefon' => json_encode(['070-1234567']),
            'is_active' => true,
            'is_telefon' => true,
            'is_ratsit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $models = HittaPersonerData::all();

    Livewire::test(ListHittaPersonerDatas::class)
        ->assertCanSeeTableRecords($models);
});
