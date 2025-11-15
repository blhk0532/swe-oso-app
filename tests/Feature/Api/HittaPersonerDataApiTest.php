<?php

use App\Models\HittaData;

it('lists hitta personer data via alias route', function () {
    HittaData::create(['personnamn' => 'Ali Test']);

    $response = $this->getJson('/api/hitta-personer-data');
    $response->assertSuccessful();
    expect($response->json('data'))->toBeArray();
});
