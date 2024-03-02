<?php

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Mail;

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
    $response = $this->postJson(apiPrefix('auth/register'),['first_name' => '']);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['message','errors']);
});

it('returns a 200 response on correct form submission and sends verification email', function () {
    $this->factory->makeUser();
    Notification::fake();

    $response = $this->postJson(apiPrefix('auth/register'), [
        'first_name' => $this->factory->getFirstName(),
        'last_name' => $this->factory->getLastName(),
        'email' => $this->factory->getEmail(),
        'password' => $this->factory->getPassword(),
        'password_confirmation' => $this->factory->getPassword(),
    ]);
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['first_name','last_name','email','token','roles','permissions','settings','is_email_verified']);
    expect($response->getContent())
    ->json()
    ->first_name->toBe($this->factory->getFirstName())
    ->last_name->toBe($this->factory->getLastName())
    ->last_name->toBe($this->factory->getLastName());

    Notification::assertSentToTimes(
        User::where('email',$this->factory->getEmail())->first(),
        VerifyEmail::class,
        1
    );

});
