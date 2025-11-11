<?php

declare(strict_types=1);

use App\Models\HittaBolag;

it('creates a HittaBolag with sni branch cast to array', function () {
    $record = HittaBolag::create([
        'juridiskt_namn' => 'Testbolaget AB',
        'org_nr' => '556000-0000',
        'registreringsdatum' => '2020-01-01',
        'bolagsform' => 'Aktiebolag',
        'sni_branch' => [['branch' => '62.01 Dataprogrammering']],
        'gatuadress' => 'Storgatan 1',
        'postnummer' => '12345',
        'postort' => 'Stockholm',
        'telefon' => '[]',
        'is_active' => true,
    ]);

    expect($record->exists)->toBeTrue()
        ->and($record->sni_branch)->toBeArray()
        ->and($record->sni_branch[0]['branch'])->toBe('62.01 Dataprogrammering');
});
