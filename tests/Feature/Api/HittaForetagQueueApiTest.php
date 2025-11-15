<?php

use App\Models\HittaForetagQueue;

it('lists hitta foretag queue records', function () {
    HittaForetagQueue::create(['post_nummer' => '500 01', 'post_ort' => 'Ort5', 'post_lan' => 'Län5', 'is_active' => true]);
    HittaForetagQueue::create(['post_nummer' => '500 02', 'post_ort' => 'Ort6', 'post_lan' => 'Län6', 'is_active' => false]);

    $response = $this->getJson('/api/hitta-foretag-queue');
    $response->assertSuccessful();
    $postNums = array_map(fn ($r) => $r['post_nummer'], $response->json('data'));
    expect($postNums)->toContain('500 01');
});

it('runForetag returns queued hitta foretag record', function () {
    HittaForetagQueue::create(['post_nummer' => '501 01', 'post_ort' => 'Ort7', 'post_lan' => 'Län7', 'foretag_queued' => 1, 'foretag_scraped' => 0]);
    $response = $this->getJson('/api/hitta-foretag-queue/run-foretag');
    $response->assertSuccessful();
    expect($response->json('data.post_nummer'))->toBe('50101');
});
