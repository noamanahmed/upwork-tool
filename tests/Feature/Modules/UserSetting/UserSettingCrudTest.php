<?php

use App\Models\UserSetting;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createUserSetting();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of usersettings', function () {
    $response = $this->getJson(apiPrefix('usersettings'));
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

it('Returns an array of usersettings with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('usersettings/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of usersetting status', function () {
    $usersettingId = $this->factory::$usersetting->id;
    $response = $this->getJson(apiPrefix('usersettings/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single usersetting with valid Id', function () {
    $usersettingId = $this->factory::$usersetting->id;
    $response = $this->getJson(apiPrefix('usersettings/'.$usersettingId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single usersetting is fetched with invalid Id', function () {
    $usersettingId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('usersettings/'.$usersettingId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single usersetting is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('usersettings'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single usersetting is being updated with incorrect data', function () {
    $usersettingId = $this->factory::$usersetting->id;
    $response = $this->patchJson(apiPrefix('usersettings/'.$usersettingId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single usersetting is created', function () {
    $this->factory->makeUserSetting();
    $response = $this->postJson(apiPrefix('usersettings'),$this->factory::$usersetting->toArray());
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


it('Returns a 200 when single usersetting is updated', function () {
    $oldUserSetting = $this->factory::$usersetting;
    $oldUserSettingId = $oldUserSetting->id;
    $this->factory->makeUserSetting();
    $response = $this->patchJson(apiPrefix('usersettings/'.$oldUserSettingId),$this->factory::$usersetting->toArray());
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
    ->name->not->toBe($oldUserSetting->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single usersetting is delete', function () {
    $usersettingId = $this->factory::$usersetting->id;
    $response = $this->deleteJson(apiPrefix('usersettings/'.$usersettingId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(UserSetting::find($usersettingId))
    ->toBeNull();
});
