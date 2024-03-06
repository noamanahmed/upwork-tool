<?php

use App\Models\Client;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createClient();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of clients', function () {
    $response = $this->getJson(apiPrefix('clients'));
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

it('Returns an array of clients with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('clients/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of client status', function () {
    $clientId = $this->factory::$client->id;
    $response = $this->getJson(apiPrefix('clients/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single client with valid Id', function () {
    $clientId = $this->factory::$client->id;
    $response = $this->getJson(apiPrefix('clients/'.$clientId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single client is fetched with invalid Id', function () {
    $clientId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('clients/'.$clientId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single client is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('clients'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single client is being updated with incorrect data', function () {
    $clientId = $this->factory::$client->id;
    $response = $this->patchJson(apiPrefix('clients/'.$clientId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single client is created', function () {
    $this->factory->makeClient();
    $response = $this->postJson(apiPrefix('clients'),$this->factory::$client->toArray());
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


it('Returns a 200 when single client is updated', function () {
    $oldClient = $this->factory::$client;
    $oldClientId = $oldClient->id;
    $this->factory->makeClient();
    $response = $this->patchJson(apiPrefix('clients/'.$oldClientId),$this->factory::$client->toArray());
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
    ->name->not->toBe($oldClient->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single client is delete', function () {
    $clientId = $this->factory::$client->id;
    $response = $this->deleteJson(apiPrefix('clients/'.$clientId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Client::find($clientId))
    ->toBeNull();
});
