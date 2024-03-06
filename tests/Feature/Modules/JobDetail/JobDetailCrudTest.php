<?php

use App\Models\JobDetail;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createJobDetail();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of jobdetails', function () {
    $response = $this->getJson(apiPrefix('jobdetails'));
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

it('Returns an array of jobdetails with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('jobdetails/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of jobdetail status', function () {
    $jobdetailId = $this->factory::$jobdetail->id;
    $response = $this->getJson(apiPrefix('jobdetails/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single jobdetail with valid Id', function () {
    $jobdetailId = $this->factory::$jobdetail->id;
    $response = $this->getJson(apiPrefix('jobdetails/'.$jobdetailId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single jobdetail is fetched with invalid Id', function () {
    $jobdetailId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('jobdetails/'.$jobdetailId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single jobdetail is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('jobdetails'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single jobdetail is being updated with incorrect data', function () {
    $jobdetailId = $this->factory::$jobdetail->id;
    $response = $this->patchJson(apiPrefix('jobdetails/'.$jobdetailId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single jobdetail is created', function () {
    $this->factory->makeJobDetail();
    $response = $this->postJson(apiPrefix('jobdetails'),$this->factory::$jobdetail->toArray());
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


it('Returns a 200 when single jobdetail is updated', function () {
    $oldJobDetail = $this->factory::$jobdetail;
    $oldJobDetailId = $oldJobDetail->id;
    $this->factory->makeJobDetail();
    $response = $this->patchJson(apiPrefix('jobdetails/'.$oldJobDetailId),$this->factory::$jobdetail->toArray());
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
    ->name->not->toBe($oldJobDetail->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single jobdetail is delete', function () {
    $jobdetailId = $this->factory::$jobdetail->id;
    $response = $this->deleteJson(apiPrefix('jobdetails/'.$jobdetailId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(JobDetail::find($jobdetailId))
    ->toBeNull();
});
