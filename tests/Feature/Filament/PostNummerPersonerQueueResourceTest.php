<?php

declare(strict_types=1);

use App\Filament\Resources\PostNummerPersonerQueues\Pages\ListPostNummerPersonerQueues;
use App\Models\PostNummerPersonerQueue;
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

test('can render postnummer personer queue list page', function () {
    Livewire::test(ListPostNummerPersonerQueues::class)
        ->assertSuccessful();
});

test('can list postnummer personer queue records', function () {
    PostNummerPersonerQueue::query()->insert([
        [
            'post_nummer' => '55555',
            'post_ort' => 'Ort E',
            'post_lan' => 'Län E',
            'merinfo_personer_saved' => 0,
            'merinfo_personer_total' => 0,
            'merinfo_status' => 'pending',
            'ratsit_personer_saved' => 0,
            'ratsit_personer_total' => 0,
            'ratsit_status' => 'pending',
            'hitta_personer_saved' => 0,
            'hitta_personer_total' => 0,
            'hitta_status' => 'pending',
            'post_nummer_personer_saved' => 0,
            'post_nummer_personer_total' => 0,
            'post_nummer_status' => 'pending',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $models = PostNummerPersonerQueue::all();

    Livewire::test(ListPostNummerPersonerQueues::class)
        ->assertCanSeeTableRecords($models);
});
