<?php

use App\Models\Region;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createRegion();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of regions', function () {
    $response = $this->getJson(apiPrefix('regions'));
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

it('Returns an array of regions with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('regions/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of region status', function () {
    $regionId = $this->factory::$region->id;
    $response = $this->getJson(apiPrefix('regions/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single region with valid Id', function () {
    $regionId = $this->factory::$region->id;
    $response = $this->getJson(apiPrefix('regions/'.$regionId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single region is fetched with invalid Id', function () {
    $regionId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('regions/'.$regionId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single region is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('regions'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single region is being updated with incorrect data', function () {
    $regionId = $this->factory::$region->id;
    $response = $this->patchJson(apiPrefix('regions/'.$regionId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single region is created', function () {
    $this->factory->makeRegion();
    $response = $this->postJson(apiPrefix('regions'),$this->factory::$region->toArray());
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


it('Returns a 200 when single region is updated', function () {
    $oldRegion = $this->factory::$region;
    $oldRegionId = $oldRegion->id;
    $this->factory->makeRegion();
    $response = $this->patchJson(apiPrefix('regions/'.$oldRegionId),$this->factory::$region->toArray());
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
    ->name->not->toBe($oldRegion->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single region is delete', function () {
    $regionId = $this->factory::$region->id;
    $response = $this->deleteJson(apiPrefix('regions/'.$regionId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Region::find($regionId))
    ->toBeNull();
});
