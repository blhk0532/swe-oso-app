<?php

use App\Models\EniroData;

test('can get eniro data index', function () {
    EniroData::query()->delete();
    EniroData::factory()->count(3)->create();

    $response = $this->get('/api/eniro-data');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
});

test('can create eniro data', function () {
    $data = [
        'personnamn' => 'Test Person',
        'alder' => '30',
        'kon' => 'M',
        'gatuadress' => 'Test Street 123',
        'postnummer' => '12345',
        'postort' => 'Test City',
    ];

    $response = $this->post('/api/eniro-data', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('eniro_data', ['personnamn' => 'Test Person']);
});
