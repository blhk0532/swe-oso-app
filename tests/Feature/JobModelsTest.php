<?php

use App\Models\Job;
use App\Models\JobBatch;

test('job model has correct table and properties', function () {
    $job = new Job;

    expect($job->getTable())->toBe('jobs');
    expect($job->getKeyName())->toBe('id');
    expect($job->getKeyType())->toBe('int');
    expect($job->usesTimestamps())->toBeFalse();
});

test('job model can be created with fillable attributes', function () {
    $data = [
        'queue' => 'default',
        'payload' => ['job' => 'TestJob', 'data' => []], // Pass array directly
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => time(),
        'created_at' => time(),
    ];

    $job = Job::create($data);

    expect($job->queue)->toBe('default');
    expect($job->attempts)->toBe(0);
    expect($job->available_at)->toBeInt();

    // Test retrieving from database - casting should work
    $retrievedJob = Job::find($job->id);
    expect($retrievedJob->payload)->toBeArray();
    expect($retrievedJob->payload['job'])->toBe('TestJob');
});

test('job batch model has correct table and properties', function () {
    $jobBatch = new JobBatch;

    expect($jobBatch->getTable())->toBe('job_batches');
    expect($jobBatch->getKeyName())->toBe('id');
    expect($jobBatch->getKeyType())->toBe('string');
    expect($jobBatch->usesTimestamps())->toBeFalse();
    expect($jobBatch->incrementing)->toBeFalse();
});

test('job batch model can be created with fillable attributes', function () {
    $uniqueId = 'test-batch-' . time() . '-' . rand(1000, 9999);

    $data = [
        'id' => $uniqueId,
        'name' => 'Test Batch',
        'total_jobs' => 10,
        'pending_jobs' => 5,
        'failed_jobs' => 0,
        'failed_job_ids' => [], // Pass array directly
        'options' => ['allowFailures' => true], // Pass array directly
        'cancelled_at' => null,
        'created_at' => time(),
        'finished_at' => null,
    ];

    $jobBatch = JobBatch::create($data);

    expect($jobBatch->id)->toBe($uniqueId);
    expect($jobBatch->name)->toBe('Test Batch');
    expect($jobBatch->total_jobs)->toBe(10);
    expect($jobBatch->pending_jobs)->toBe(5);
    expect($jobBatch->failed_jobs)->toBe(0);
    expect($jobBatch->cancelled_at)->toBeNull();
    expect($jobBatch->finished_at)->toBeNull();

    // Test retrieving from database - casting should work
    $retrievedBatch = JobBatch::find($jobBatch->id);
    expect($retrievedBatch->failed_job_ids)->toBeArray();
    expect($retrievedBatch->options)->toBeArray();
    expect($retrievedBatch->options['allowFailures'])->toBeTrue();
});
