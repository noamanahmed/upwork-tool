<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createUser();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of users', function () {
    $response = $this->getJson(apiPrefix('users'));
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

it('Returns an array of users with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('users/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of user status', function () {
    $userId = $this->factory::$user->id;
    $response = $this->getJson(apiPrefix('users/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns an array of user type', function () {
    $userId = $this->factory::$user->id;
    $response = $this->getJson(apiPrefix('users/type/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single user with valid Id', function () {
    $userId = $this->factory::$user->id;
    $response = $this->getJson(apiPrefix('users/'.$userId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['first_name']);
});


it('Returns a 404 when single user is fetched with invalid Id', function () {
    $userId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('users/'.$userId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single user is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('users'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single user is being updated with incorrect data', function () {
    $userId = $this->factory::$user->id;
    $response = $this->patchJson(apiPrefix('users/'.$userId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single user is created', function () {

    $user = $this->factory->makeUser();

    $user = $user->makeVisible('password')->toArray();
    $user['password'] = $this->factory::PASSWORD;
    $user['password_confirmation'] = $this->factory::PASSWORD;
    $user['role'] = Role::available()->pluck('id')->first();

    $response = $this->postJson(apiPrefix('users'),$user);
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'first_name',
    ]);
    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});


it('Returns a 200 when single user is updated', function () {
    $oldUser = $this->factory::$user;
    $oldUserId = $oldUser->id;
    $this->factory->makeUser();
    $user = $this->factory::$user->toArray();
    $user['role_id'] = Role::available()->pluck('id')->first();
    $response = $this->patchJson(apiPrefix('users/'.$oldUserId),$user);
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'first_name',
    ]);

    expect($response->getContent())
    ->json()
    ->first_name->not->toBe($oldUser->first_name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single user is delete', function () {
    $userId = $this->factory::$user->id;
    $response = $this->deleteJson(apiPrefix('users/'.$userId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(User::find($userId))
    ->toBeNull();
});
