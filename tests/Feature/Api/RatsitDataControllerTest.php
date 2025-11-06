<?php

use App\Models\RatsitData;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can list ratsit data records', function () {
    $records = RatsitData::factory()->count(3)->create();

    actingAs($this->user)
        ->getJson('/api/ratsit-data')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'address',
                    'person',
                    'property',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('can filter ratsit data by city', function () {
    $record1 = RatsitData::factory()->create(['bo_postort' => 'Stockholm']);
    $record2 = RatsitData::factory()->create(['bo_postort' => 'Gothenburg']);

    actingAs($this->user)
        ->getJson('/api/ratsit-data?bo_postort=Stockholm')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $record1->id);
});

it('can create a ratsit data record', function () {
    $data = [
        'ps_fornamn' => 'Rita',
        'ps_efternamn' => 'Svensson',
        'ps_personnamn' => 'Rita Svensson',
        'bo_postnummer' => '12345',
        'bo_postort' => 'Stockholm',
        'ps_telefon' => ['+46123456789'],
        'ps_epost_adress' => ['rita@example.com'],
        'is_active' => true,
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $data)
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'address',
                'person',
                'property',
            ],
        ]);

    assertDatabaseHas('ratsit_data', [
        'ps_fornamn' => 'Rita',
        'ps_efternamn' => 'Svensson',
        'bo_postnummer' => '12345',
    ]);
});

it('requires authentication to access ratsit endpoints', function () {
    getJson('/api/ratsit-data')
        ->assertUnauthorized();

    postJson('/api/ratsit-data', [])
        ->assertUnauthorized();
});
