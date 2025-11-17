<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;

it('does not show Shop and Blog in sidebar', function () {
    Filament::setCurrentPanel('admin');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
    $response->assertDontSee('Shop');
    $response->assertDontSee('Blog');
});
