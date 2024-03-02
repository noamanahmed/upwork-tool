<?php

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;

beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createUserSettings();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a 200 to get settings', function () {
    $response = $this->getJson(apiPrefix('account/settings'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        "timezone",
        "language"
    ]);
});

it('updates the settings and get a 200 response', function () {

    $timezone = $this->factory->getUserSettingTimezone();
    $language = $this->factory->getUserSettingLanguage();
    $response = $this->patchJson(apiPrefix('account/settings'),[
        'timezone' => 'Asia/Karachi',
        'language' => 'it',
    ]);

    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        "timezone",
        "language"
    ]);

    expect($response->getContent())
    ->json()
    ->timezone->not->toBe($timezone)
    ->language->not->toBe($language)
    ->timezone->toBe('Asia/Karachi')
    ->language->toBe('it');
});
it('updates the settings with the password and get a 200 response', function () {

    $timezone = $this->factory->getUserSettingTimezone();
    $language = $this->factory->getUserSettingLanguage();
    $response = $this->patchJson(apiPrefix('account/settings'),[
        'timezone' => 'Asia/Karachi',
        'language' => 'it',
        'old_password' => $this->factory->getPassword(),
        'password' => $this->factory->getNewPassword(),
        'password_confirmation' => $this->factory->getNewPassword(),
    ]);

    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        "timezone",
        "language"
    ]);

    expect($response->getContent())
    ->json()
    ->timezone->not->toBe($timezone)
    ->language->not->toBe($language)
    ->timezone->toBe('Asia/Karachi')
    ->language->toBe('it');

    $user = User::where('email',$this->factory->getEmail())->firstOrFail();
    $this->assertTrue(Hash::check($this->factory->getNewPassword(),$user->password));
    $this->assertFalse(Hash::check($this->factory->getPassword(),$user->password));
});
