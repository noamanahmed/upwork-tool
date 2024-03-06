<?php

use App\Models\Proxy;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createProxy();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of proxys', function () {
    $response = $this->getJson(apiPrefix('proxys'));
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

it('Returns an array of proxys with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('proxys/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of proxy status', function () {
    $proxyId = $this->factory::$proxy->id;
    $response = $this->getJson(apiPrefix('proxys/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single proxy with valid Id', function () {
    $proxyId = $this->factory::$proxy->id;
    $response = $this->getJson(apiPrefix('proxys/'.$proxyId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single proxy is fetched with invalid Id', function () {
    $proxyId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('proxys/'.$proxyId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single proxy is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('proxys'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single proxy is being updated with incorrect data', function () {
    $proxyId = $this->factory::$proxy->id;
    $response = $this->patchJson(apiPrefix('proxys/'.$proxyId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single proxy is created', function () {
    $this->factory->makeProxy();
    $response = $this->postJson(apiPrefix('proxys'),$this->factory::$proxy->toArray());
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


it('Returns a 200 when single proxy is updated', function () {
    $oldProxy = $this->factory::$proxy;
    $oldProxyId = $oldProxy->id;
    $this->factory->makeProxy();
    $response = $this->patchJson(apiPrefix('proxys/'.$oldProxyId),$this->factory::$proxy->toArray());
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
    ->name->not->toBe($oldProxy->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single proxy is delete', function () {
    $proxyId = $this->factory::$proxy->id;
    $response = $this->deleteJson(apiPrefix('proxys/'.$proxyId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Proxy::find($proxyId))
    ->toBeNull();
});
