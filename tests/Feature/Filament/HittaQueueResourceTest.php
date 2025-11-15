<?php

declare(strict_types=1);

use App\Filament\Resources\HittaQueues\Pages\CreateHittaQueue;
use App\Filament\Resources\HittaQueues\Pages\ListHittaQueues;
use App\Models\HittaQueue;
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

test('can render hitta queue list page', function () {
    Livewire::test(ListHittaQueues::class)
        ->assertSuccessful();
});

test('can list hitta queue records', function () {
    $records = HittaQueue::factory()->count(3)->create();

    Livewire::test(ListHittaQueues::class)
        ->assertCanSeeTableRecords($records);
});

test('can search hitta queue by post nummer', function () {
    $records = HittaQueue::factory()->count(3)->create();
    $firstRecord = $records->first();

    Livewire::test(ListHittaQueues::class)
        ->searchTable($firstRecord->post_nummer)
        ->assertCanSeeTableRecords([$firstRecord])
        ->assertCanNotSeeTableRecords($records->skip(1));
});

test('can filter hitta queue by active status', function () {
    $activeRecords = HittaQueue::factory()->count(2)->create(['is_active' => true]);
    $inactiveRecords = HittaQueue::factory()->count(2)->create(['is_active' => false]);

    Livewire::test(ListHittaQueues::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords($activeRecords)
        ->assertCanNotSeeTableRecords($inactiveRecords);
});

test('can render create hitta queue page', function () {
    Livewire::test(CreateHittaQueue::class)
        ->assertSuccessful();
});

test('can create hitta queue record', function () {
    $newData = [
        'post_nummer' => '123 45',
        'post_ort' => 'Stockholm',
        'post_lan' => 'Stockholm',
        'foretag_total' => 100,
        'personer_total' => 500,
        'personer_house' => 50,
        'foretag_phone' => 80,
        'personer_phone' => 400,
        'foretag_saved' => 0,
        'personer_saved' => 0,
        'foretag_queued' => false,
        'personer_queued' => false,
        'foretag_scraped' => false,
        'personer_scraped' => false,
        'is_active' => true,
    ];

    Livewire::test(CreateHittaQueue::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas(HittaQueue::class, [
        'post_nummer' => '123 45',
        'post_ort' => 'Stockholm',
        'post_lan' => 'Stockholm',
    ]);
});

test('can validate required fields when creating hitta queue', function () {
    Livewire::test(CreateHittaQueue::class)
        ->fillForm([
            'post_nummer' => '',
            'post_ort' => '',
            'post_lan' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'post_nummer' => ['required'],
            'post_ort' => ['required'],
            'post_lan' => ['required'],
        ]);
});

test('can queue foretag via bulk action', function () {
    $records = HittaQueue::factory()->count(3)->create(['foretag_queued' => false]);

    Livewire::test(ListHittaQueues::class)
        ->callTableBulkAction('queueForetag', $records->pluck('id')->all())
        ->assertNotified();

    foreach ($records as $record) {
        expect($record->refresh()->foretag_queued)->toBeTrue();
    }
});

test('can queue personer via bulk action', function () {
    $records = HittaQueue::factory()->count(3)->create(['personer_queued' => false]);

    Livewire::test(ListHittaQueues::class)
        ->callTableBulkAction('queuePersoner', $records->pluck('id')->all())
        ->assertNotified();

    foreach ($records as $record) {
        expect($record->refresh()->personer_queued)->toBeTrue();
    }
});
