<?php

use App\Models\DataPrivate;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can list data private records', function () {
    $records = DataPrivate::factory()->count(5)->create();

    actingAs($this->user)
        ->getJson('/api/data-private')
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

it('can filter data private records by postnummer', function () {
    $record1 = DataPrivate::factory()->create(['bo_postnummer' => '12345']);
    $record2 = DataPrivate::factory()->create(['bo_postnummer' => '67890']);

    actingAs($this->user)
        ->getJson('/api/data-private?bo_postnummer=12345')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $record1->id);
});

it('can filter data private records by is_active', function () {
    $activeRecord = DataPrivate::factory()->create(['is_active' => true]);
    $inactiveRecord = DataPrivate::factory()->create(['is_active' => false]);

    actingAs($this->user)
        ->getJson('/api/data-private?is_active=1')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeRecord->id);
});

it('can create a data private record', function () {
    $data = [
        'ps_fornamn' => 'John',
        'ps_efternamn' => 'Doe',
        'ps_personnamn' => 'John Doe',
        'bo_postnummer' => '12345',
        'bo_postort' => 'Stockholm',
        'ps_telefon' => ['+46123456789'],
        'ps_epost_adress' => ['john@example.com'],
        'is_active' => true,
    ];

    actingAs($this->user)
        ->postJson('/api/data-private', $data)
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'address',
                'person',
                'property',
            ],
        ]);

    assertDatabaseHas('data_private', [
        'ps_fornamn' => 'John',
        'ps_efternamn' => 'Doe',
        'bo_postnummer' => '12345',
    ]);
});

it('can show a specific data private record', function () {
    $record = DataPrivate::factory()->create();

    actingAs($this->user)
        ->getJson("/api/data-private/{$record->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $record->id);
});

it('can update a data private record', function () {
    $record = DataPrivate::factory()->create([
        'ps_fornamn' => 'John',
        'bo_postnummer' => '12345',
    ]);

    $updateData = [
        'ps_fornamn' => 'Jane',
        'bo_postnummer' => '67890',
    ];

    actingAs($this->user)
        ->putJson("/api/data-private/{$record->id}", $updateData)
        ->assertSuccessful()
        ->assertJsonPath('data.person.first_name', 'Jane');

    assertDatabaseHas('data_private', [
        'id' => $record->id,
        'ps_fornamn' => 'Jane',
        'bo_postnummer' => '67890',
    ]);
});

it('can delete a data private record', function () {
    $record = DataPrivate::factory()->create();

    actingAs($this->user)
        ->deleteJson("/api/data-private/{$record->id}")
        ->assertSuccessful();

    assertDatabaseMissing('data_private', [
        'id' => $record->id,
    ]);
});

it('requires authentication to access api endpoints', function () {
    getJson('/api/data-private')
        ->assertUnauthorized();

    postJson('/api/data-private', [])
        ->assertUnauthorized();
});

it('validates required fields when creating', function () {
    actingAs($this->user)
        ->postJson('/api/data-private', [
            'ps_kon' => 'Invalid',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ps_kon']);
});

it('validates json array fields', function () {
    actingAs($this->user)
        ->postJson('/api/data-private', [
            'ps_epost_adress' => ['invalid-email'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ps_epost_adress.0']);
});
