<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createSetting();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of settings', function () {
    $response = $this->getJson(apiPrefix('settings'));
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

it('Returns an array of settings with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('settings/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of setting status', function () {
    $settingId = $this->factory::$setting->id;
    $response = $this->getJson(apiPrefix('settings/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single setting with valid Id', function () {
    $settingId = $this->factory::$setting->id;
    $response = $this->getJson(apiPrefix('settings/'.$settingId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single setting is fetched with invalid Id', function () {
    $settingId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('settings/'.$settingId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single setting is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('settings'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single setting is being updated with incorrect data', function () {
    $settingId = $this->factory::$setting->id;
    $response = $this->patchJson(apiPrefix('settings/'.$settingId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single setting is created', function () {
    $this->factory->makeSetting();
    $response = $this->postJson(apiPrefix('settings'),$this->factory::$setting->toArray());
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


it('Returns a 200 when single setting is updated', function () {
    $oldSetting = $this->factory::$setting;
    $oldSettingId = $oldSetting->id;
    $this->factory->makeSetting();
    $response = $this->patchJson(apiPrefix('settings/'.$oldSettingId),$this->factory::$setting->toArray());
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
    ->name->not->toBe($oldSetting->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single setting is delete', function () {
    $settingId = $this->factory::$setting->id;
    $response = $this->deleteJson(apiPrefix('settings/'.$settingId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Setting::find($settingId))
    ->toBeNull();
});
