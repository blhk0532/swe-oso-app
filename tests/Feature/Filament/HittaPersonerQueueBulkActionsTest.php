<?php

declare(strict_types=1);

use App\Filament\Resources\HittaPersonerQueues\Pages\ListHittaPersonerQueues;
use App\Jobs\RunHittaCountsForPersonerQueue;
use App\Jobs\RunHittaPersonsDataForQueue;
use App\Jobs\RunHittaRatsitForPersonerQueue;
use App\Models\HittaPersonerQueue;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can bulk run hitta counts for personer', function () {
    Bus::fake();

    HittaPersonerQueue::query()->insert(array_map(function ($i) {
        return [
            'post_nummer' => '111 1' . $i,
            'post_ort' => 'Ort',
            'post_lan' => 'Lan',
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
        ];
    }, range(1, 3)));

    $records = HittaPersonerQueue::all();

    Livewire::test(ListHittaPersonerQueues::class)
        ->callTableBulkAction('bulkRunHittaCount', $records->pluck('id')->all())
        ->assertNotified('Hitta Counts Queued');

    Bus::assertDispatchedTimes(RunHittaCountsForPersonerQueue::class, 3);
});

test('can bulk run hitta persons data and skip running', function () {
    Bus::fake();

    HittaPersonerQueue::query()->insert(array_map(function ($i) {
        return [
            'post_nummer' => '222 2' . $i,
            'post_ort' => 'Ort',
            'post_lan' => 'Lan',
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
        ];
    }, range(1, 3)));

    $records = HittaPersonerQueue::all();
    $records->first()->update(['personer_status' => 'running']);

    Livewire::test(ListHittaPersonerQueues::class)
        ->callTableBulkAction('bulkRunHittaPersonsData', $records->pluck('id')->all())
        ->assertNotified('Persons Data Queued');

    // one record is running and should be skipped
    Bus::assertDispatchedTimes(RunHittaPersonsDataForQueue::class, 2);
});

test('can bulk run hitta+ratsit and autostart queue', function () {
    Bus::fake();

    HittaPersonerQueue::query()->insert(array_map(function ($i) {
        return [
            'post_nummer' => '333 3' . $i,
            'post_ort' => 'Ort',
            'post_lan' => 'Lan',
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
        ];
    }, range(1, 2)));

    $records = HittaPersonerQueue::all();

    Livewire::test(ListHittaPersonerQueues::class)
        ->callTableBulkAction('bulkRunHittaRatsit', $records->pluck('id')->all())
        ->assertNotified('Hitta+Ratsit Scrapers Queued');

    Bus::assertDispatchedTimes(RunHittaRatsitForPersonerQueue::class, 2);
});
