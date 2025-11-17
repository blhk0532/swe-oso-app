<?php

use App\Models\HittaData;

test('is_hus field defaults to false', function () {
    $hittaData = HittaData::create([
        'personnamn' => 'Test Person',
        'is_active' => true,
    ]);

    // Check the fresh model from database
    $freshModel = $hittaData->fresh();
    expect($freshModel->is_hus)->toBeFalse();
});

test('is_hus field can be set to true', function () {
    $hittaData = HittaData::create([
        'personnamn' => 'Test Person',
        'is_hus' => true,
        'is_active' => true,
    ]);

    expect($hittaData->is_hus)->toBeTrue();
});

test('is_hus field is cast to boolean', function () {
    $hittaData = new HittaData(['personnamn' => 'Test', 'is_hus' => '1', 'is_active' => true]);
    $hittaData->save();

    expect($hittaData->fresh()->is_hus)->toBeTrue();

    $hittaData->update(['is_hus' => '0']);
    expect($hittaData->fresh()->is_hus)->toBeFalse();
});
