<?php

use App\Models\RatsitData;

it('lists ratsit personer data via alias route', function () {
    RatsitData::create(['personnamn' => 'Ratsit Test']);

    $response = $this->getJson('/api/ratsit-personer-data');
    $response->assertSuccessful();
    expect($response->json('data'))->toBeArray();
});
