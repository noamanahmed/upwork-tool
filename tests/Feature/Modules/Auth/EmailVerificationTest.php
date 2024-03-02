<?php

use App\Enums\UserStatusEnum;
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

it('returns a 422 if email is already verified', function () {
    $response = $this->getJson(apiPrefix('auth/resend-verification-email'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['email']);
});


it('returns a 200 response on correct form submission and re-sends email verification', function () {
    $this->factory::$user->email_verified_at = null;
    $this->factory::$user->status = UserStatusEnum::EMAIL_UNVERIFIED->value;
    $this->factory::$user->email_verification_token = Hash::make(Str::random(32));
    $this->factory::$user->save();

    Notification::fake();

    $response = $this->getJson(apiPrefix('auth/resend-verification-email'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();

    $user = User::where('email',$this->factory->getEmail())->firstOrFail();
    expect($user->email_verified_at)->toBeNull();
    expect($user->email_verification_token)->not->toBeNull();

    Notification::assertSentToTimes(
        $user,
        VerifyEmail::class,
        1
    );

});

it('returns a 401 response on when email verification token is incorrectly submitted', function () {
    $this->factory::$user->email_verification_token = Hash::make(Str::random(32));
    $this->factory::$user->save();

    $response = $this->postJson(apiPrefix('auth/verify-email'),[
        'token' => Hash::make(Str::random(32))
    ]);
    expect($response->status())->toBe(401);
    expect($response->getContent())
    ->toBeJson();

    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['token']);

});

it('returns a 200 response on when email verification token is submitted and mark\'s the user as active', function () {
    $this->factory::$user->email_verified_at = null;
    $this->factory::$user->status = UserStatusEnum::EMAIL_UNVERIFIED->value;
    $this->factory::$user->email_verification_token = Hash::make(Str::random(32));
    $this->factory::$user->save();

    $response = $this->postJson(apiPrefix('auth/verify-email'),[
        'token' => $this->factory::$user->email_verification_token
    ]);

    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();

    $user = User::where('email',$this->factory->getEmail())->firstOrFail();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->email_verification_token)->toBeNull();
});
