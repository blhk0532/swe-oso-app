<?php

use App\Models\HittaSe;

test('is_hus field defaults to false', function () {
    $hittaSe = HittaSe::create([
        'personnamn' => 'Test Person',
        'is_active' => true,
    ]);

    // Check the fresh model from database
    $freshModel = $hittaSe->fresh();
    expect($freshModel->is_hus)->toBeFalse();
});

test('is_hus field can be set to true', function () {
    $hittaSe = HittaSe::create([
        'personnamn' => 'Test Person',
        'is_hus' => true,
        'is_active' => true,
    ]);

    expect($hittaSe->is_hus)->toBeTrue();
});

test('is_hus field is cast to boolean', function () {
    $hittaSe = new HittaSe(['personnamn' => 'Test', 'is_hus' => '1', 'is_active' => true]);
    $hittaSe->save();

    expect($hittaSe->fresh()->is_hus)->toBeTrue();

    $hittaSe->update(['is_hus' => '0']);
    expect($hittaSe->fresh()->is_hus)->toBeFalse();
});
