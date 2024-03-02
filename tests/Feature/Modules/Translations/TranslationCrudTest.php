<?php

use App\Models\Language;

beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a response of translations', function () {
    $response = $this->getJson(apiPrefix('translations'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->each->toHaveKeys(['id','language_id','language_code','key','value']);
});


it('returns a filtered response of translations', function () {
    $response = $this->getJson(apiPrefix('translations').'?filter[language.code]=en');
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->not->toBeEmpty();
    expect($response->json())
    ->each->toHaveKey('language_code','en-USA');
    expect($response->json())
    ->each->not->toHaveKey('language_code','nl');
    expect($response->json())
    ->each->toHaveKeys(['id','language_id','language_code','key','value']);

});


it('returns an empty response on invalid langage code', function () {
    $response = $this->getJson(apiPrefix('translations').'?filter[language.code]=test123');
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toBeEmpty();
});
