<?php

use App\Models\MerinfoData;

it('lists merinfo personer data via alias route', function () {
    MerinfoData::create(['personnamn' => 'Merinfo Test']);

    $response = $this->getJson('/api/merinfo-personer-data');
    $response->assertSuccessful();
    expect($response->json('data'))->toBeArray();
});
