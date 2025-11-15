<?php

declare(strict_types=1);

use App\Models\HittaQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

it('returns 404 when no personer jobs are queued', function () {
    // no matching records
    getJson('/api/hitta-queue/run-personer')
        ->assertNotFound()
        ->assertJsonPath('data', null);
});

it('returns the first queued personer job', function () {
    // non-matching
    HittaQueue::factory()->create([
        'personer_queued' => false,
        'personer_scraped' => false,
    ]);

    // matching (older)
    $older = HittaQueue::factory()->create([
        'post_nummer' => '123 45',
        'personer_queued' => true,
        'personer_scraped' => false,
    ]);

    // matching (newer)
    HittaQueue::factory()->create([
        'post_nummer' => '234 56',
        'personer_queued' => true,
        'personer_scraped' => false,
    ]);

    getJson('/api/hitta-queue/run-personer')
        ->assertOk()
        ->assertJsonPath('data.id', $older->id)
        // API returns post_nummer without space
        ->assertJsonPath('data.post_nummer', '12345');
});

it('can bulk update by post_nummer list', function () {
    HittaQueue::factory()->create([
        'post_nummer' => '555 55',
        'personer_saved' => 0,
        'foretag_queued' => false,
        'is_active' => false,
    ]);

    $payload = [
        'records' => [
            [
                'post_nummer' => '55555', // unformatted
                'personer_saved' => 7,
                'foretag_queued' => 1,
                'is_active' => true,
            ],
        ],
    ];

    postJson('/api/hitta-queue/bulk-update', $payload)
        ->assertOk()
        ->assertJsonPath('summary.updated', 1)
        ->assertJsonPath('summary.failed', 0);

    assertDatabaseHas('hitta_queue', [
        'post_nummer' => '555 55',
        'personer_saved' => 7,
        'foretag_queued' => 1,
        'is_active' => 1,
    ]);
});

it('can update a single record by post_nummer path parameter', function () {
    HittaQueue::factory()->create([
        'post_nummer' => '777 77',
        'personer_queued' => true,
        'is_active' => true,
    ]);

    $update = [
        'personer_queued' => 0,
        'is_active' => false,
    ];

    putJson('/api/hitta-queue/update/77777', $update)
        ->assertOk()
        ->assertJsonPath('message', 'HittaQueue record updated successfully');

    assertDatabaseHas('hitta_queue', [
        'post_nummer' => '777 77',
        'personer_queued' => 0,
        'is_active' => 0,
    ]);
});
