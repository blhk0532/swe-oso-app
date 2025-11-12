<?php

use App\Models\UpplysningData;

test('can get upplysning data index', function () {
    UpplysningData::query()->delete();
    UpplysningData::factory()->count(3)->create();

    $response = $this->get('/api/upplysning-data');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
});

test('can create upplysning data', function () {
    $data = [
        'personnamn' => 'Test Person',
        'alder' => '30',
        'kon' => 'M',
        'gatuadress' => 'Test Street 123',
        'postnummer' => '12345',
        'postort' => 'Test City',
    ];

    $response = $this->post('/api/upplysning-data', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('upplysning_data', ['personnamn' => 'Test Person']);
});
