<?php

declare(strict_types=1);

use App\Models\RatsitQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

it('returns 404 when no personer jobs are queued (ratsit)', function () {
    getJson('/api/ratsit-queue/run-personer')
        ->assertNotFound()
        ->assertJsonPath('data', null);
});

it('returns the first queued personer job (ratsit)', function () {
    // ensure enum values align with migration by overriding statuses
    RatsitQueue::factory()->create([
        'personer_queued' => false,
        'personer_scraped' => false,
        'personer_status' => 'pending',
        'foretag_status' => 'pending',
    ]);

    $older = RatsitQueue::factory()->create([
        'post_nummer' => '321 09',
        'personer_queued' => true,
        'personer_scraped' => false,
        'personer_status' => 'pending',
        'foretag_status' => 'pending',
    ]);

    RatsitQueue::factory()->create([
        'post_nummer' => '654 32',
        'personer_queued' => true,
        'personer_scraped' => false,
        'personer_status' => 'pending',
        'foretag_status' => 'pending',
    ]);

    getJson('/api/ratsit-queue/run-personer')
        ->assertOk()
        ->assertJsonPath('data.id', $older->id)
        ->assertJsonPath('data.post_nummer', '32109');
});

it('can bulk update by post_nummer list (ratsit)', function () {
    RatsitQueue::factory()->create([
        'post_nummer' => '111 22',
        'personer_saved' => 0,
        'foretag_queued' => false,
        'is_active' => false,
        'personer_status' => 'pending',
        'foretag_status' => 'pending',
    ]);

    $payload = [
        'records' => [
            [
                'post_nummer' => '11122',
                'personer_saved' => 3,
                'foretag_queued' => 1,
                'is_active' => true,
            ],
        ],
    ];

    postJson('/api/ratsit-queue/bulk-update', $payload)
        ->assertOk()
        ->assertJsonPath('summary.updated', 1)
        ->assertJsonPath('summary.failed', 0);

    assertDatabaseHas('ratsit_queue', [
        'post_nummer' => '111 22',
        'personer_saved' => 3,
        'foretag_queued' => 1,
        'is_active' => 1,
    ]);
});

it('can update a single record by post_nummer path parameter (ratsit)', function () {
    RatsitQueue::factory()->create([
        'post_nummer' => '987 65',
        'personer_queued' => true,
        'is_active' => true,
        'personer_status' => 'pending',
        'foretag_status' => 'pending',
    ]);

    $update = [
        'personer_queued' => 0,
        'is_active' => false,
    ];

    putJson('/api/ratsit-queue/update/98765', $update)
        ->assertOk()
        ->assertJsonPath('message', 'RatsitQueue record updated successfully');

    assertDatabaseHas('ratsit_queue', [
        'post_nummer' => '987 65',
        'personer_queued' => 0,
        'is_active' => 0,
    ]);
});
