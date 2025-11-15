<?php

use App\Models\RatsitForetagQueue;

it('lists ratsit foretag queue records', function () {
    RatsitForetagQueue::create(['post_nummer' => '600 01', 'post_ort' => 'OrtR1', 'post_lan' => 'LänR1', 'is_active' => true]);
    RatsitForetagQueue::create(['post_nummer' => '600 02', 'post_ort' => 'OrtR2', 'post_lan' => 'LänR2', 'is_active' => false]);

    $response = $this->getJson('/api/ratsit-foretag-queue');
    $response->assertSuccessful();
    $postNums = array_map(fn ($r) => $r['post_nummer'], $response->json('data'));
    expect($postNums)->toContain('600 01');
});

it('runForetag returns queued ratsit foretag record', function () {
    RatsitForetagQueue::create(['post_nummer' => '601 01', 'post_ort' => 'OrtR3', 'post_lan' => 'LänR3', 'foretag_queued' => 1, 'foretag_scraped' => 0]);
    $response = $this->getJson('/api/ratsit-foretag-queue/run-foretag');
    $response->assertSuccessful();
    expect($response->json('data.post_nummer'))->toBe('60101');
});
