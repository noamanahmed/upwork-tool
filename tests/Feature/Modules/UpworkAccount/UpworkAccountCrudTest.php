<?php

use App\Models\UpworkAccount;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createUpworkAccount();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of upworkaccounts', function () {
    $response = $this->getJson(apiPrefix('upworkaccounts'));
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

it('Returns an array of upworkaccounts with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('upworkaccounts/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of upworkaccount status', function () {
    $upworkaccountId = $this->factory::$upworkaccount->id;
    $response = $this->getJson(apiPrefix('upworkaccounts/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single upworkaccount with valid Id', function () {
    $upworkaccountId = $this->factory::$upworkaccount->id;
    $response = $this->getJson(apiPrefix('upworkaccounts/'.$upworkaccountId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single upworkaccount is fetched with invalid Id', function () {
    $upworkaccountId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('upworkaccounts/'.$upworkaccountId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single upworkaccount is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('upworkaccounts'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single upworkaccount is being updated with incorrect data', function () {
    $upworkaccountId = $this->factory::$upworkaccount->id;
    $response = $this->patchJson(apiPrefix('upworkaccounts/'.$upworkaccountId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single upworkaccount is created', function () {
    $this->factory->makeUpworkAccount();
    $response = $this->postJson(apiPrefix('upworkaccounts'),$this->factory::$upworkaccount->toArray());
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


it('Returns a 200 when single upworkaccount is updated', function () {
    $oldUpworkAccount = $this->factory::$upworkaccount;
    $oldUpworkAccountId = $oldUpworkAccount->id;
    $this->factory->makeUpworkAccount();
    $response = $this->patchJson(apiPrefix('upworkaccounts/'.$oldUpworkAccountId),$this->factory::$upworkaccount->toArray());
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
    ->name->not->toBe($oldUpworkAccount->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single upworkaccount is delete', function () {
    $upworkaccountId = $this->factory::$upworkaccount->id;
    $response = $this->deleteJson(apiPrefix('upworkaccounts/'.$upworkaccountId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(UpworkAccount::find($upworkaccountId))
    ->toBeNull();
});
