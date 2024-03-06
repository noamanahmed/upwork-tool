<?php

use App\Models\Job;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createJob();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of jobs', function () {
    $response = $this->getJson(apiPrefix('jobs'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['data','meta']);
    expect($response->getContent())
    ->json()
    ->meta
    ->toBePaginatorResponse();
});

it('Returns an array of jobs with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('jobs/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of job status', function () {
    $jobId = $this->factory::$job->id;
    $response = $this->getJson(apiPrefix('jobs/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single job with valid Id', function () {
    $jobId = $this->factory::$job->id;
    $response = $this->getJson(apiPrefix('jobs/'.$jobId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single job is fetched with invalid Id', function () {
    $jobId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('jobs/'.$jobId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single job is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('jobs'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single job is being updated with incorrect data', function () {
    $jobId = $this->factory::$job->id;
    $response = $this->patchJson(apiPrefix('jobs/'.$jobId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single job is created', function () {
    $this->factory->makeJob();
    $response = $this->postJson(apiPrefix('jobs'),$this->factory::$job->toArray());
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});


it('Returns a 200 when single job is updated', function () {
    $oldJob = $this->factory::$job;
    $oldJobId = $oldJob->id;
    $this->factory->makeJob();
    $response = $this->patchJson(apiPrefix('jobs/'.$oldJobId),$this->factory::$job->toArray());
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);

    expect($response->getContent())
    ->json()
    ->name->not->toBe($oldJob->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single job is delete', function () {
    $jobId = $this->factory::$job->id;
    $response = $this->deleteJson(apiPrefix('jobs/'.$jobId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Job::find($jobId))
    ->toBeNull();
});
