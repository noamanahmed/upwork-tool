<?php

use App\Models\JobSearch;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createJobSearch();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of jobsearchs', function () {
    $response = $this->getJson(apiPrefix('jobsearchs'));
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

it('Returns an array of jobsearchs with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('jobsearchs/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of jobsearch status', function () {
    $jobsearchId = $this->factory::$jobsearch->id;
    $response = $this->getJson(apiPrefix('jobsearchs/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single jobsearch with valid Id', function () {
    $jobsearchId = $this->factory::$jobsearch->id;
    $response = $this->getJson(apiPrefix('jobsearchs/'.$jobsearchId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single jobsearch is fetched with invalid Id', function () {
    $jobsearchId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('jobsearchs/'.$jobsearchId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single jobsearch is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('jobsearchs'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single jobsearch is being updated with incorrect data', function () {
    $jobsearchId = $this->factory::$jobsearch->id;
    $response = $this->patchJson(apiPrefix('jobsearchs/'.$jobsearchId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single jobsearch is created', function () {
    $this->factory->makeJobSearch();
    $response = $this->postJson(apiPrefix('jobsearchs'),$this->factory::$jobsearch->toArray());
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


it('Returns a 200 when single jobsearch is updated', function () {
    $oldJobSearch = $this->factory::$jobsearch;
    $oldJobSearchId = $oldJobSearch->id;
    $this->factory->makeJobSearch();
    $response = $this->patchJson(apiPrefix('jobsearchs/'.$oldJobSearchId),$this->factory::$jobsearch->toArray());
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
    ->name->not->toBe($oldJobSearch->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single jobsearch is delete', function () {
    $jobsearchId = $this->factory::$jobsearch->id;
    $response = $this->deleteJson(apiPrefix('jobsearchs/'.$jobsearchId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(JobSearch::find($jobsearchId))
    ->toBeNull();
});
