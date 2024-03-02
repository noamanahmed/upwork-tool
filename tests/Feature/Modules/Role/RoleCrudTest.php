<?php

use App\Models\Role;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createRole();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of roles', function () {
    $response = $this->getJson(apiPrefix('roles'));
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

it('Returns an array of roles with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('roles/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of role status', function () {
    $roleId = $this->factory::$role->id;
    $response = $this->getJson(apiPrefix('roles/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single role with valid Id', function () {
    $roleId = $this->factory::$role->id;
    $response = $this->getJson(apiPrefix('roles/'.$roleId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['name']);
});


it('Returns a 404 when single role is fetched with invalid Id', function () {
    $roleId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('roles/'.$roleId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single role is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('roles'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single role is being updated with incorrect data', function () {
    $roleId = $this->factory::$role->id;
    $response = $this->patchJson(apiPrefix('roles/'.$roleId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single role is created', function () {
    $this->factory->makeRole();
    $response = $this->postJson(apiPrefix('roles'),$this->factory::$role->toArray());
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
    ]);
    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});


it('Returns a 200 when single role is updated', function () {
    $oldRole = $this->factory::$role;
    $oldRoleId = $oldRole->id;
    $this->factory->makeRole();
    $response = $this->patchJson(apiPrefix('roles/'.$oldRoleId),$this->factory::$role->toArray());
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name'
    ]);

    expect($response->getContent())
    ->json()
    ->name->not->toBe($oldRole->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single role is delete', function () {
    $roleId = $this->factory::$role->id;
    $response = $this->deleteJson(apiPrefix('roles/'.$roleId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Role::find($roleId))
    ->toBeNull();
});
