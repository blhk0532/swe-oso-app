<?php

declare(strict_types=1);

use App\Filament\Resources\RatsitPersonerQueues\Pages\ListRatsitPersonerQueues;
use App\Models\RatsitPersonerQueue;
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

test('can render ratsit personer queue list page', function () {
    Livewire::test(ListRatsitPersonerQueues::class)
        ->assertSuccessful();
});

test('can list ratsit personer queue records', function () {
    RatsitPersonerQueue::query()->insert([
        [
            'post_nummer' => '444 44',
            'post_ort' => 'Ort D',
            'post_lan' => 'Län D',
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

    $models = RatsitPersonerQueue::all();

    Livewire::test(ListRatsitPersonerQueues::class)
        ->assertCanSeeTableRecords($models);
});
