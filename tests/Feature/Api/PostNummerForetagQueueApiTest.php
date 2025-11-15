<?php

use App\Models\PostNummerForetagQueue;

it('lists postnummer foretag queue records', function () {
    $pn1 = (string) random_int(10000, 99999);
    $pn2 = (string) random_int(10000, 99999);

    PostNummerForetagQueue::create(['post_nummer' => $pn1, 'post_ort' => 'OrtPN1', 'post_lan' => 'LänPN1', 'merinfo_foretag_total' => 5]);
    PostNummerForetagQueue::create(['post_nummer' => $pn2, 'post_ort' => 'OrtPN2', 'post_lan' => 'LänPN2', 'merinfo_foretag_total' => 0]);

    $response = $this->getJson('/api/postnummer-foretag-queue');
    $response->assertSuccessful();
    $postNums = array_map(fn ($r) => $r['post_nummer'], $response->json('data'));
    expect($postNums)->toContain($pn1);
});

it('runForetag returns a record for postnummer foretag processing', function () {
    $pnu = (string) random_int(10000, 99999);
    PostNummerForetagQueue::create(['post_nummer' => $pnu, 'post_ort' => 'OrtPN3', 'post_lan' => 'LänPN3', 'merinfo_foretag_total' => 5]);
    $response = $this->getJson('/api/postnummer-foretag-queue/run-foretag');
    $response->assertSuccessful();
    $json = $response->json('data');
    expect($json['merinfo_foretag_total'])->toBeGreaterThan(0);
});
