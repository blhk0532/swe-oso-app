<?php

declare(strict_types=1);

use App\Filament\Resources\RatsitPersonerDatas\Pages\ListRatsitPersonerDatas;
use App\Models\RatsitPersonerData;
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

test('can render ratsit personer data list page', function () {
    Livewire::test(ListRatsitPersonerDatas::class)
        ->assertSuccessful();
});

test('can list ratsit personer data records', function () {
    RatsitPersonerData::query()->insert([
        [
            'personnamn' => 'Ratsit Person A',
            'personnummer' => '19900101-1234',
            'fornamn' => 'Ratsit',
            'efternamn' => 'A',
            'gatuadress' => 'Ratsit Address A',
            'postnummer' => '555 55',
            'postort' => 'Ort E',
            'telefon' => json_encode([]),
            'is_active' => true,
            'is_telefon' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'personnamn' => 'Ratsit Person B',
            'personnummer' => '19850202-2222',
            'fornamn' => 'Ratsit',
            'efternamn' => 'B',
            'gatuadress' => 'Ratsit Address B',
            'postnummer' => '666 66',
            'postort' => 'Ort F',
            'telefon' => json_encode(['070-2223334']),
            'is_active' => true,
            'is_telefon' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $models = RatsitPersonerData::all();

    Livewire::test(ListRatsitPersonerDatas::class)
        ->assertCanSeeTableRecords($models);
});
