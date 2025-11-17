<?php

use App\Jobs\UpdatePostNummersTable;
use App\Models\PostNummer;
use App\Models\User;

test('job sends success notification when completed', function () {
    // Create a test user
    $user = User::factory()->create();

    // Create a test PostNummer record
    PostNummer::factory()->create(['is_active' => true]);

    // Get initial notification count
    $initialCount = DB::table('notifications')->count();

    // Dispatch and process the job
    $job = new UpdatePostNummersTable('mount', [], now()->toISOString());
    $job->handle();

    // Check that a new notification was created
    $newCount = DB::table('notifications')->count();
    expect($newCount)->toBeGreaterThan($initialCount);

    // Check that the notification contains the correct job name
    $latestNotification = DB::table('notifications')->latest('created_at')->first();
    expect($latestNotification)->not->toBeNull();

    $data = json_decode($latestNotification->data, true);
    expect($data['title'])->toContain('Post Nummers Update');
});

test('job sends failure notification when it fails', function () {
    // Create a test user
    User::factory()->create();

    // Get initial notification count
    $initialCount = DB::table('notifications')->count();

    // Create a job that will fail
    $job = new UpdatePostNummersTable('mount', [], now()->toISOString());

    // Simulate a failure
    $exception = new Exception('Test failure');
    $job->failed($exception);

    // Check that a new notification was created
    $newCount = DB::table('notifications')->count();
    expect($newCount)->toBeGreaterThan($initialCount);

    // Check that the notification contains failure information
    $latestNotification = DB::table('notifications')->latest('created_at')->first();
    $data = json_decode($latestNotification->data, true);
    expect($data['title'])->toContain('Job Failed');
});

test('notification contains correct job details', function () {
    // Create a test user
    User::factory()->create();
    PostNummer::factory()->create(['is_active' => true]);

    // Dispatch and process the job
    $timestamp = now()->toISOString();
    $job = new UpdatePostNummersTable('mount', [], $timestamp);
    $job->handle();

    // Retrieve the latest notification
    $latestNotification = DB::table('notifications')->latest('created_at')->first();
    $data = json_decode($latestNotification->data, true);

    expect($data)->toHaveKeys(['title', 'body']);
    expect($data['title'])->toContain('Post Nummers Update');
    expect($data['body'])->toContain('Event: mount');
});
