<?php

use App\Models\MerinfoQueue;

it('lists merinfo queue records', function () {
    MerinfoQueue::create(['post_nummer' => '111 22', 'post_ort' => 'Ort A', 'post_lan' => 'Län A', 'is_active' => true]);
    MerinfoQueue::create(['post_nummer' => '222 33', 'post_ort' => 'Ort B', 'post_lan' => 'Län B', 'is_active' => false]);

    $response = $this->getJson('/api/merinfo-queue');
    $response->assertSuccessful();
    $json = $response->json();
    $postNums = array_map(fn ($r) => $r['post_nummer'], $json['data']);
    expect($postNums)->toContain('111 22');
    expect($postNums)->toContain('222 33');
});

it('filters merinfo queue by is_active', function () {
    MerinfoQueue::create(['post_nummer' => '333 44', 'post_ort' => 'Ort C', 'post_lan' => 'Län C', 'is_active' => true]);
    MerinfoQueue::create(['post_nummer' => '444 55', 'post_ort' => 'Ort D', 'post_lan' => 'Län D', 'is_active' => false]);

    $response = $this->getJson('/api/merinfo-queue?is_active=1');
    $response->assertSuccessful();
    $data = $response->json()['data'];
    $postNums = array_map(fn ($r) => $r['post_nummer'], $data);
    expect($postNums)->toContain('333 44');
});

it('shows a single queue record', function () {
    $record = MerinfoQueue::create(['post_nummer' => '555 66', 'post_ort' => 'Ort E', 'post_lan' => 'Län E', 'is_active' => true]);
    $response = $this->getJson('/api/merinfo-queue/' . $record->id);
    $response->assertSuccessful();
    expect($response->json('data.post_nummer'))->toBe('555 66');
});

it('runPersoner returns 404 when none queued', function () {
    $response = $this->getJson('/api/merinfo-queue/run-personer');
    $response->assertNotFound();
});

it('runPersoner returns a queued record', function () {
    MerinfoQueue::create([
        'post_nummer' => '777 88',
        'post_ort' => 'Ort F',
        'post_lan' => 'Län F',
        'personer_queued' => 1,
        'personer_scraped' => 0,
    ]);

    $response = $this->getJson('/api/merinfo-queue/run-personer');
    $response->assertSuccessful();
    expect($response->json('data.post_nummer'))->toBe('77788'); // stripped spaces
});

it('updates record by postnummer normalization', function () {
    MerinfoQueue::create([
        'post_nummer' => '123 45',
        'post_ort' => 'Ort G',
        'post_lan' => 'Län G',
        'personer_total' => 10,
    ]);

    $payload = ['personer_total' => 20];
    $response = $this->putJson('/api/merinfo-queue/update/12345', $payload); // without space
    $response->assertSuccessful();
    expect(MerinfoQueue::where('post_nummer', '123 45')->first()->personer_total)->toBe(20);
});

it('bulk updates multiple records', function () {
    MerinfoQueue::create(['post_nummer' => '900 01', 'post_ort' => 'Ort H1', 'post_lan' => 'Län H', 'personer_total' => 1]);
    MerinfoQueue::create(['post_nummer' => '900 02', 'post_ort' => 'Ort H2', 'post_lan' => 'Län H', 'personer_total' => 2]);

    $payload = [
        'records' => [
            ['post_nummer' => '90001', 'personer_total' => 10],
            ['post_nummer' => '90002', 'personer_total' => 20],
        ],
    ];
    $response = $this->postJson('/api/merinfo-queue/bulk-update', $payload);
    $response->assertSuccessful();
    $summary = $response->json('summary');
    expect($summary['updated'])->toBe(2);
    expect(MerinfoQueue::where('post_nummer', '900 01')->first()->personer_total)->toBe(10);
});
