<?php

declare(strict_types=1);

use App\Models\HittaSe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('shows truncated phone preview with ellipsis when longer than 13 chars', function () {
    $user = User::factory()->create();
    actingAs($user);

    $record = HittaSe::create([
        'personnamn' => 'Test Person',
        'alder' => 30,
        'kon' => 'Man',
        'gatuadress' => 'Example Street 1',
        'postnummer' => '123 45',
        'postort' => 'Testville',
        'telefon' => [
            '070-720 41 43',
            '070-225 41 97',
        ],
        'is_active' => true,
        'is_telefon' => true,
        'is_ratsit' => false,
    ]);

    $response = get('/hitta-ses');

    $response->assertSuccessful();

    // Full joined version (for reference): 070-720 41 43 | 070-225 41 97 (length > 13)
    // Preview should be first 13 chars + ellipsis
    expect($record->telefon_preview)->toBe(mb_substr('070-720 41 43 | 070-225 41 97', 0, 13) . '…');

    $response->assertSee($record->telefon_preview);
});

it('shows em dash when phone empty or placeholder', function () {
    $user = User::factory()->create();
    actingAs($user);

    $record = HittaSe::create([
        'personnamn' => 'No Phone',
        'alder' => 40,
        'kon' => 'Kvinna',
        'gatuadress' => 'Example Street 2',
        'postnummer' => '543 21',
        'postort' => 'Elsewhere',
        'telefon' => [],
        'is_active' => true,
        'is_telefon' => false,
        'is_ratsit' => false,
    ]);

    $response = get('/hitta-ses');
    $response->assertSuccessful();

    expect($record->telefon_preview)->toBe('—');
    $response->assertSee('—');
});
