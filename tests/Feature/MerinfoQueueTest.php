<?php

use App\Models\MerinfoQueue;

test('can create merinfo queue record', function () {
    $data = [
        'post_nummer' => '12345',
        'post_ort' => 'Stockholm',
        'post_lan' => 'Stockholms län',
        'foretag_total' => 100,
        'personer_total' => 500,
        'foretag_phone' => 50,
        'personer_phone' => 200,
        'foretag_saved' => 25,
        'personer_saved' => 100,
        'foretag_queued' => 10,
        'personer_queued' => 50,
        'foretag_scraped' => true,
        'personer_scraped' => false,
        'is_active' => true,
    ];

    $merinfoQueue = MerinfoQueue::create($data);

    expect($merinfoQueue)->toBeInstanceOf(MerinfoQueue::class);
    expect($merinfoQueue->post_nummer)->toBe('12345');
    expect($merinfoQueue->post_ort)->toBe('Stockholm');
    expect($merinfoQueue->post_lan)->toBe('Stockholms län');
    expect($merinfoQueue->foretag_total)->toBe(100);
    expect($merinfoQueue->personer_total)->toBe(500);
    expect($merinfoQueue->foretag_scraped)->toBeTrue();
    expect($merinfoQueue->personer_scraped)->toBeFalse();
    expect($merinfoQueue->is_active)->toBeTrue();
});

test('merinfo queue has correct fillable attributes', function () {
    $merinfoQueue = new MerinfoQueue;

    $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'foretag_total',
        'personer_total',
        'personer_house',
        'foretag_phone',
        'personer_phone',
        'foretag_saved',
        'personer_saved',
        'personer_pages',
        'personer_page',
        'personer_status',
        'foretag_status',
        'foretag_queued',
        'personer_queued',
        'foretag_scraped',
        'personer_scraped',
        'is_active',
    ];

    expect($merinfoQueue->getFillable())->toBe($fillable);
});

test('merinfo queue has correct cast attributes', function () {
    $merinfoQueue = new MerinfoQueue;

    $casts = $merinfoQueue->getCasts();

    expect($casts['foretag_scraped'])->toBe('boolean');
    expect($casts['personer_scraped'])->toBe('boolean');
    expect($casts['is_active'])->toBe('boolean');
});
