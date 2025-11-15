<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jeffgreco13\FilamentBreezy\Pages\MyProfilePage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('loads the profile page without errors', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MyProfilePage::class)
        ->assertOk();
});
