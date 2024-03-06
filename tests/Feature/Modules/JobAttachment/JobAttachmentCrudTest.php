<?php

use App\Models\JobAttachment;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createJobAttachment();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of jobattachments', function () {
    $response = $this->getJson(apiPrefix('jobattachments'));
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

it('Returns an array of jobattachments with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('jobattachments/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of jobattachment status', function () {
    $jobattachmentId = $this->factory::$jobattachment->id;
    $response = $this->getJson(apiPrefix('jobattachments/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single jobattachment with valid Id', function () {
    $jobattachmentId = $this->factory::$jobattachment->id;
    $response = $this->getJson(apiPrefix('jobattachments/'.$jobattachmentId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single jobattachment is fetched with invalid Id', function () {
    $jobattachmentId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('jobattachments/'.$jobattachmentId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single jobattachment is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('jobattachments'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single jobattachment is being updated with incorrect data', function () {
    $jobattachmentId = $this->factory::$jobattachment->id;
    $response = $this->patchJson(apiPrefix('jobattachments/'.$jobattachmentId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single jobattachment is created', function () {
    $this->factory->makeJobAttachment();
    $response = $this->postJson(apiPrefix('jobattachments'),$this->factory::$jobattachment->toArray());
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


it('Returns a 200 when single jobattachment is updated', function () {
    $oldJobAttachment = $this->factory::$jobattachment;
    $oldJobAttachmentId = $oldJobAttachment->id;
    $this->factory->makeJobAttachment();
    $response = $this->patchJson(apiPrefix('jobattachments/'.$oldJobAttachmentId),$this->factory::$jobattachment->toArray());
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
    ->name->not->toBe($oldJobAttachment->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single jobattachment is delete', function () {
    $jobattachmentId = $this->factory::$jobattachment->id;
    $response = $this->deleteJson(apiPrefix('jobattachments/'.$jobattachmentId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(JobAttachment::find($jobattachmentId))
    ->toBeNull();
});
