<?php

declare(strict_types=1);

use App\Filament\Resources\MerinfoPersonerQueues\Pages\ListMerinfoPersonerQueues;
use App\Models\MerinfoPersonerQueue;
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

test('can render merinfo personer queue list page', function () {
    Livewire::test(ListMerinfoPersonerQueues::class)
        ->assertSuccessful();
});

test('can list merinfo personer queue records', function () {
    MerinfoPersonerQueue::query()->insert([
        [
            'post_nummer' => '333 33',
            'post_ort' => 'Ort C',
            'post_lan' => 'Län C',
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

    $models = MerinfoPersonerQueue::all();

    Livewire::test(ListMerinfoPersonerQueues::class)
        ->assertCanSeeTableRecords($models);
});
