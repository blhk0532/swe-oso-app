<?php

declare(strict_types=1);

use App\Models\PostNummerQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

it('can bulk update postnummer queue records', function () {
    PostNummerQueue::factory()->create([
        'post_nummer' => '246 80',
        'merinfo_queued' => false,
        'ratsit_queued' => false,
        'hitta_queued' => false,
        'post_nummer_queued' => false,
        'is_active' => false,
    ]);

    $payload = [
        'records' => [
            [
                'post_nummer' => '24680',
                'merinfo_queued' => true,
                'ratsit_queued' => true,
                'hitta_queued' => false,
                'post_nummer_queued' => true,
                'is_active' => true,
            ],
        ],
    ];

    postJson('/api/postnummer-queue/bulk-update', $payload)
        ->assertOk()
        ->assertJsonPath('summary.updated', 1)
        ->assertJsonPath('summary.failed', 0);

    assertDatabaseHas('post_nummer_queue', [
        'post_nummer' => '246 80',
        'merinfo_queued' => 1,
        'ratsit_queued' => 1,
        'hitta_queued' => 0,
        'post_nummer_queued' => 1,
        'is_active' => 1,
    ]);
});

it('can update a single postnummer queue record by code', function () {
    PostNummerQueue::factory()->create([
        'post_nummer' => '135 79',
        'merinfo_complete' => false,
        'is_active' => true,
    ]);

    $payload = [
        'merinfo_complete' => true,
        'is_active' => false,
    ];

    putJson('/api/postnummer-queue/update/13579', $payload)
        ->assertOk()
        ->assertJsonPath('message', 'PostNummerQueue record updated successfully');

    assertDatabaseHas('post_nummer_queue', [
        'post_nummer' => '135 79',
        'merinfo_complete' => 1,
        'is_active' => 0,
    ]);
});
