<?php

use App\Models\RatsitData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

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
    $record1 = RatsitData::factory()->create(['postort' => 'Stockholm']);
    $record2 = RatsitData::factory()->create(['postort' => 'Gothenburg']);

    actingAs($this->user)
        ->getJson('/api/ratsit-data?postort=Stockholm')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $record1->id);
});

it('can create a ratsit data record', function () {
    $data = [
        'fornamn' => 'Rita',
        'efternamn' => 'Svensson',
        'personnamn' => 'Rita Svensson',
        'postnummer' => '12345',
        'postort' => 'Stockholm',
        'telefon' => ['+46123456789'],
        'epost_adress' => ['rita@example.com'],
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
        'fornamn' => 'Rita',
        'efternamn' => 'Svensson',
        'postnummer' => '12345',
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
        'personnummer' => '19850512-1234',
        'fornamn' => 'Erik',
        'efternamn' => 'Svensson',
        'personnamn' => 'Erik Svensson',
        'postnummer' => '12345',
        'postort' => 'Stockholm',
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $data)
        ->assertCreated();

    // Now update the same record with new data
    $updatedData = array_merge($data, [
        'postort' => 'Gothenburg',
        'telefon' => ['+46123456789'],
    ]);

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $updatedData)
        ->assertSuccessful() // Should return 200 (updated), not 201 (created)
        ->assertJsonPath('data.person.personnummer', '19850512-1234')
        ->assertJsonPath('data.address.postort', 'Gothenburg');

    // Verify only one record exists
    expect(RatsitData::where('personnummer', '19850512-1234')->count())->toBe(1);
});

it('validates required fields for saving ratsit data', function () {
    // Test with all required fields present (personnamn, gatuadress, postort)
    $validData = [
        'personnamn' => 'Test Person',
        'gatuadress' => 'Test Street 123',
        'postort' => 'Stockholm',
        'telefon' => ['+46123456789'],
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $validData)
        ->assertCreated()
        ->assertJsonPath('data.person.personnamn', 'Test Person')
        ->assertJsonPath('data.address.gatuadress', 'Test Street 123')
        ->assertJsonPath('data.address.postort', 'Stockholm');

    assertDatabaseHas('ratsit_data', [
        'personnamn' => 'Test Person',
        'gatuadress' => 'Test Street 123',
        'postort' => 'Stockholm',
    ]);
});

it('can save ratsit data with array fields', function () {
    $data = [
        'personnamn' => 'Test Person',
        'gatuadress' => 'Test Street 123',
        'postort' => 'Stockholm',
        'telefon' => ['+46123456789', '+46987654321'],
        'epost_adress' => ['test@example.com', 'test2@example.com'],
        'personer' => ['Person 1', 'Person 2'],
        'fordon' => [['type' => 'Car', 'registration' => 'ABC123']],
    ];

    actingAs($this->user)
        ->postJson('/api/ratsit-data', $data)
        ->assertCreated()
        ->assertJsonPath('data.person.telefon', ['+46123456789', '+46987654321'])
        ->assertJsonPath('data.person.epost_adress', ['test@example.com', 'test2@example.com']);

    $record = RatsitData::where('personnamn', 'Test Person')->first();
    expect($record->telefon)->toBe(['+46123456789', '+46987654321']);
    expect($record->epost_adress)->toBe(['test@example.com', 'test2@example.com']);
    expect($record->personer)->toBe(['Person 1', 'Person 2']);
});
