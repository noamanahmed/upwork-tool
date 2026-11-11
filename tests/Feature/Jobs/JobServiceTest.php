<?php

use App\Models\Job;
use App\Models\JobSearchPivot;
use App\Services\JobService;

test('inserting the same upwork response twice only creates one job and one pivot', function () {
    $search = upworkTestJobSearch();
    $edges = [upworkTestEdge(1001)];

    $service = app(JobService::class);

    $firstJobs = $service->insertJobsFromApiResponse($edges);
    $firstPivots = $service->attachJobsToJobSearchesFromApiResponse($edges, $search);

    expect($firstJobs)->toHaveCount(1)
        ->and($firstPivots)->toHaveCount(1)
        ->and(Job::count())->toBe(1)
        ->and(JobSearchPivot::count())->toBe(1);

    $secondJobs = $service->insertJobsFromApiResponse($edges);
    $secondPivots = $service->attachJobsToJobSearchesFromApiResponse($edges, $search);

    expect($secondJobs)->toHaveCount(0)
        ->and($secondPivots)->toBe([])
        ->and(Job::count())->toBe(1)
        ->and(JobSearchPivot::count())->toBe(1);
});

test('attaching the same job to a second search creates a second pivot only', function () {
    $firstSearch = upworkTestJobSearch();
    $secondSearch = upworkTestJobSearch();
    $edges = [upworkTestEdge(1002)];

    $service = app(JobService::class);

    $service->insertJobsFromApiResponse($edges);
    $service->attachJobsToJobSearchesFromApiResponse($edges, $firstSearch);
    $secondPivots = $service->attachJobsToJobSearchesFromApiResponse($edges, $secondSearch);

    expect($secondPivots)->toHaveCount(1)
        ->and(Job::count())->toBe(1)
        ->and(JobSearchPivot::count())->toBe(2);
});
