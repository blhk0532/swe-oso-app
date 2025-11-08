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

it('can upsert ratsit data by personnummer', function () {
    // First create a record
    $data = [
        'ps_personnummer' => '19850512-1234',
        'ps_fornamn' => 'Erik',
        'ps_efternamn' => 'Svensson',
        'ps_personnamn' => 'Erik Svensson',
        'bo_postnummer' => '12345',
        'bo_postort' => 'Stockholm',
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $data)
        ->assertCreated();

    // Now update the same record with new data
    $updatedData = array_merge($data, [
        'bo_postort' => 'Gothenburg',
        'ps_telefon' => ['+46123456789'],
    ]);

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $updatedData)
        ->assertSuccessful() // Should return 200 (updated), not 201 (created)
        ->assertJsonPath('data.person.ps_personnummer', '19850512-1234')
        ->assertJsonPath('data.address.bo_postort', 'Gothenburg');

    // Verify only one record exists
    expect(RatsitData::where('ps_personnummer', '19850512-1234')->count())->toBe(1);
});

it('validates required fields for saving ratsit data', function () {
    // Test with all required fields present (personnamn, gatuadress, postort)
    $validData = [
        'ps_personnamn' => 'Test Person',
        'bo_gatuadress' => 'Test Street 123',
        'bo_postort' => 'Stockholm',
        'ps_telefon' => ['+46123456789'],
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $validData)
        ->assertCreated()
        ->assertJsonPath('data.person.ps_personnamn', 'Test Person')
        ->assertJsonPath('data.address.bo_gatuadress', 'Test Street 123')
        ->assertJsonPath('data.address.bo_postort', 'Stockholm');

    assertDatabaseHas('ratsit_data', [
        'ps_personnamn' => 'Test Person',
        'bo_gatuadress' => 'Test Street 123',
        'bo_postort' => 'Stockholm',
    ]);
});

it('can save ratsit data with array fields', function () {
    $data = [
        'ps_personnamn' => 'Test Person',
        'bo_gatuadress' => 'Test Street 123',
        'bo_postort' => 'Stockholm',
        'ps_telefon' => ['+46123456789', '+46987654321'],
        'ps_epost_adress' => ['test@example.com', 'test2@example.com'],
        'bo_personer' => ['Person 1', 'Person 2'],
        'bo_fordon' => [['type' => 'Car', 'registration' => 'ABC123']],
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $data)
        ->assertCreated()
        ->assertJsonPath('data.person.ps_telefon', ['+46123456789', '+46987654321'])
        ->assertJsonPath('data.person.ps_epost_adress', ['test@example.com', 'test2@example.com']);

    $record = RatsitData::where('ps_personnamn', 'Test Person')->first();
    expect($record->ps_telefon)->toBe(['+46123456789', '+46987654321']);
    expect($record->ps_epost_adress)->toBe(['test@example.com', 'test2@example.com']);
    expect($record->bo_personer)->toBe(['Person 1', 'Person 2']);
});
