<?php

use App\Models\MerinfoData;

it('lists merinfo data records (empty)', function () {
    $response = $this->getJson('/api/merinfo-data');
    $response->assertSuccessful();
    expect($response->json('data'))->toBeArray();
});

it('creates a merinfo data record', function () {
    $payload = [
        'personnamn' => 'Test Person',
        'postnummer' => '111 22',
        'postort' => 'Stockholm',
        'telefon' => ['0701234567'],
        'is_active' => true,
    ];
    $response = $this->postJson('/api/merinfo-data', $payload);
    $response->assertCreated();
    expect(MerinfoData::where('personnamn', 'Test Person')->exists())->toBeTrue();
});

it('shows a merinfo data record', function () {
    $record = MerinfoData::create(['personnamn' => 'Alpha']);
    $response = $this->getJson('/api/merinfo-data/' . $record->id);
    $response->assertSuccessful();
    expect($response->json('data.personnamn'))->toBe('Alpha');
});

it('updates a merinfo data record', function () {
    $record = MerinfoData::create(['personnamn' => 'Beta']);
    $response = $this->putJson('/api/merinfo-data/' . $record->id, ['personnamn' => 'Beta Updated']);
    $response->assertSuccessful();
    expect($record->refresh()->personnamn)->toBe('Beta Updated');
});

it('destroys a merinfo data record', function () {
    $record = MerinfoData::create(['personnamn' => 'Gamma']);
    $response = $this->deleteJson('/api/merinfo-data/' . $record->id);
    $response->assertSuccessful();
    expect(MerinfoData::find($record->id))->toBeNull();
});

it('bulk stores merinfo data records', function () {
    $payload = [
        'records' => [
            ['personnamn' => 'Bulk One', 'postort' => 'City'],
            ['name' => 'Bulk Two', 'city' => 'Town'], // uses alternative keys
        ],
    ];
    $response = $this->postJson('/api/merinfo-data/bulk', $payload);
    $response->assertSuccessful();
    expect(MerinfoData::where('personnamn', 'Bulk One')->exists())->toBeTrue();
    expect(MerinfoData::where('personnamn', 'Bulk Two')->exists())->toBeTrue();
});

it('bulk updates totals for merinfo data records', function () {
    $a = MerinfoData::create(['personnamn' => 'Totals A']);
    $b = MerinfoData::create(['personnamn' => 'Totals B']);
    $payload = [
        'records' => [
            ['id' => $a->id, 'merinfo_personer_total' => 10],
            ['id' => $b->id, 'merinfo_foretag_total' => 5],
        ],
    ];
    $response = $this->postJson('/api/merinfo-data/bulk-update-totals', $payload);
    $response->assertSuccessful();
    expect($a->refresh()->merinfo_personer_total)->toBe(10);
    expect($b->refresh()->merinfo_foretag_total)->toBe(5);
});
