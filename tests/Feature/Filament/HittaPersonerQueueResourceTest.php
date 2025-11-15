<?php

declare(strict_types=1);

use App\Filament\Resources\HittaPersonerQueues\Pages\ListHittaPersonerQueues;
use App\Models\HittaPersonerQueue;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can render hitta personer queue list page', function () {
    Livewire::test(ListHittaPersonerQueues::class)
        ->assertSuccessful();
});

test('can list hitta personer queue records', function () {
    $records = HittaPersonerQueue::query()->insert([
        [
            'post_nummer' => '111 11',
            'post_ort' => 'Ort A',
            'post_lan' => 'Län A',
            'personer_phone' => 0,
            'personer_house' => 0,
            'personer_saved' => 0,
            'personer_total' => 0,
            'personer_page' => 0,
            'personer_pages' => 0,
            'personer_status' => 'pending',
            'personer_queued' => false,
            'personer_scraped' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'post_nummer' => '222 22',
            'post_ort' => 'Ort B',
            'post_lan' => 'Län B',
            'personer_phone' => 0,
            'personer_house' => 0,
            'personer_saved' => 0,
            'personer_total' => 0,
            'personer_page' => 0,
            'personer_pages' => 0,
            'personer_status' => 'pending',
            'personer_queued' => false,
            'personer_scraped' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $models = HittaPersonerQueue::all();

    Livewire::test(ListHittaPersonerQueues::class)
        ->assertCanSeeTableRecords($models);
});
