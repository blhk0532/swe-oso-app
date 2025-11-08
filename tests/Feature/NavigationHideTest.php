<?php

declare(strict_types=1);

use App\Models\User;

it('does not show Shop and Blog in sidebar', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
    $response->assertDontSee('Shop');
    $response->assertDontSee('Blog');
});
