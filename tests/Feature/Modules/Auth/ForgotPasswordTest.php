<?php

use App\Models\User;
use App\Notifications\ResetPassword;

beforeEach(function(){
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ]);
});

beforeEach(function(){
    $this->factory = getFactory();
});

it('returns a 422 response on incorrect data', function () {
    $this->factory->createUser();
    $response = $this->postJson(apiPrefix('auth/forgot-password'),['email' => 'incorrectEmailFormat']);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['message','errors']);
});


it('returns a 200 response on correct form submission and send forgot password email', function () {
    $this->factory->createUser();
    Notification::fake();

    $response = $this->postJson(apiPrefix('auth/forgot-password'), [
        'email' => $this->factory->getEmail()
    ]);
    expect($response->status())->toBe(200);

    Notification::assertSentToTimes(
        User::where('email',$this->factory->getEmail())->first(),
        ResetPassword::class,
        1
    );

});

it('returns a 200 response on password resent and change\'s password correctly', function () {
    $this->factory->createUser();
    $token = Password::createToken($this->factory::$user);
    $user = User::where('email',$this->factory->getEmail())->firstOrFail();
    $response = $this->postJson(apiPrefix('auth/reset-password'), [
        'email' => $this->factory->getEmail(),
        'token' => $token,
        'password' => $this->factory->getNewPassword(),
        'password_confirmation' => $this->factory->getNewPassword(),
    ]);

    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json());

    $user = User::where('email',$this->factory->getEmail())->firstOrFail();
    $this->assertTrue(Hash::check($this->factory->getNewPassword(),$user->password));
    $this->assertFalse(Hash::check($this->factory->getPassword(),$user->password));

});
