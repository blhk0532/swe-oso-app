<?php

declare(strict_types=1);

use App\Jobs\RunHittaCountForPostNummer;
use App\Models\PostNummer;
use Illuminate\Support\Facades\Process;

it('updates post nummer with counts from script output', function () {
    // Avoid hitting real broadcaster
    config()->set('broadcasting.default', 'log');
    // Arrange: create a simple PostNummer row
    // Ensure no conflicting existing record (DB may be seeded)
    PostNummer::where('post_nummer', '12345')->delete();
    $record = PostNummer::create([
        'post_nummer' => '12345',
        'post_ort' => 'Testby',
        'post_lan' => 'Testlän',
    ]);

    // Fake Process to return a JSON counts line in stdout
    Process::fake([
        // Any command returns this result
        '*' => Process::result(
            output: "Some logs...\n{\"hittaForetag\": 3, \"hittaPersoner\": 2, \"hittaPlatser\": 1}\n",
            errorOutput: '',
            exitCode: 0,
        ),
    ]);

    // Act: run the job handle
    $job = new RunHittaCountForPostNummer($record);
    $job->handle();

    // Assert: record updated with totals and mapped fields
    $record->refresh();
    expect($record->total_count)->toBe(3)
        ->and($record->bolag)->toBe(3);
});
