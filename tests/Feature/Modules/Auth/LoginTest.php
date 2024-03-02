<?php

use App\Enums\UserStatusEnum;

beforeEach(function(){
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ]);
});

beforeEach(function(){
    $this->factory = getFactory();
});

it('returns a 401 response on incorrect login parameters', function () {
    $this->factory->createUser();
    $response = $this->postJson(apiPrefix('auth/login'), [
        'email' => $this->factory->getEmail(),
        'password' => $this->factory->getPassword().'_incorrect',
    ]);
    expect($response->status())->toBe(401);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['message','errors','errors.email']);
});


it('returns a 401 response on correct login parameters but if user is banned', function () {
    $this->factory->createUser();
    $this->factory::$user->status = UserStatusEnum::BLOCKED;
    $this->factory::$user->save();

    $response = $this->postJson(apiPrefix('auth/login'), [
        'email' => $this->factory->getEmail(),
        'password' => $this->factory->getPassword(),
    ]);
    expect($response->status())->toBe(401);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['message','errors','errors.email']);
});
it('returns a 200 response on correct login parameters', function () {
    $this->factory->createUser();
    $this->factory::$user->status = UserStatusEnum::ACTIVE;
    $this->factory::$user->save();

    $response = $this->postJson(apiPrefix('auth/login'), [
        'email' => $this->factory->getEmail(),
        'password' => $this->factory->getPassword(),
    ]);
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['first_name','last_name','email','token','roles','permissions','settings','is_email_verified']);
});
