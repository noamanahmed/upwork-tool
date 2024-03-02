<?php

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;

beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});

it('returns a 200 to get profile data', function () {
    $response = $this->getJson(apiPrefix('account/profile'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        "first_name",
        "last_name",
        "email",
        "status",
        "is_email_verified"
    ]);
});

it('updates the profile and get a 200 response', function () {

    $response = $this->patchJson(apiPrefix('account/profile'),[
        'first_name' => $this->factory->getFirstName().'_changed',
        'last_name' => $this->factory->getLastName().'_changed',
    ]);


    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        "first_name",
        "last_name",
        "email",
        "status",
        "is_email_verified"
    ]);

    expect($response->getContent())
    ->json()
    ->first_name->not->toBe($this->factory->getFirstName())
    ->last_name->not->toBe($this->factory->getLastName())
    ->first_name->toBe($this->factory->getFirstName().'_changed')
    ->last_name->toBe($this->factory->getLastName().'_changed');
});
