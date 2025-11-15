<?php

use App\Models\MerinfoForetagQueue;

it('lists foretag queue records', function () {
    MerinfoForetagQueue::create(['post_nummer' => '800 01', 'post_ort' => 'Ort1', 'post_lan' => 'Län1', 'is_active' => true]);
    MerinfoForetagQueue::create(['post_nummer' => '800 02', 'post_ort' => 'Ort2', 'post_lan' => 'Län2', 'is_active' => false]);

    $response = $this->getJson('/api/merinfo-foretag-queue');
    $response->assertSuccessful();
    $postNums = array_map(fn ($r) => $r['post_nummer'], $response->json('data'));
    expect($postNums)->toContain('800 01');
    expect($postNums)->toContain('800 02');
});

it('runForetag returns 404 when none queued', function () {
    // Ensure no records are marked as queued for foretag
    MerinfoForetagQueue::query()->update(['foretag_queued' => false]);

    $response = $this->getJson('/api/merinfo-foretag-queue/run-foretag');
    $response->assertNotFound();
});

it('runForetag returns queued record', function () {
    MerinfoForetagQueue::create(['post_nummer' => '801 01', 'post_ort' => 'Ort3', 'post_lan' => 'Län3', 'foretag_queued' => 1, 'foretag_scraped' => 0]);
    $response = $this->getJson('/api/merinfo-foretag-queue/run-foretag');
    $response->assertSuccessful();
    expect($response->json('data.post_nummer'))->toBe('80101');
});

it('updates a foretag record by postnummer normalization', function () {
    MerinfoForetagQueue::create(['post_nummer' => '802 01', 'post_ort' => 'Ort4', 'post_lan' => 'Län4', 'foretag_total' => 1]);

    $payload = ['foretag_total' => 42];
    $response = $this->putJson('/api/merinfo-foretag-queue/update/80201', $payload);
    $response->assertSuccessful();
    expect(MerinfoForetagQueue::where('post_nummer', '802 01')->first()->foretag_total)->toBe(42);
});

it('bulk updates multiple foretag records', function () {
    MerinfoForetagQueue::create(['post_nummer' => '803 01', 'post_ort' => 'OrtA', 'post_lan' => 'LänA', 'foretag_total' => 1]);
    MerinfoForetagQueue::create(['post_nummer' => '803 02', 'post_ort' => 'OrtB', 'post_lan' => 'LänB', 'foretag_total' => 2]);

    $payload = [
        'records' => [
            ['post_nummer' => '80301', 'foretag_total' => 10],
            ['post_nummer' => '80302', 'foretag_total' => 20],
        ],
    ];

    $response = $this->postJson('/api/merinfo-foretag-queue/bulk-update', $payload);
    $response->assertSuccessful();
    $summary = $response->json('summary');
    expect($summary['updated'])->toBe(2);
});
