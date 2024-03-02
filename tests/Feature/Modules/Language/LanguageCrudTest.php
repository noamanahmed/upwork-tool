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


it('returns a paginated response of languages', function () {
    $response = $this->getJson(apiPrefix('languages'));
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

it('Returns an array of languages with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('languages/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});



it('Returns a single language with valid Id', function () {
    $languageId = Language::inRandomOrder()->firstOrFail()->id;
    $response = $this->getJson(apiPrefix('languages/'.$languageId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'code',
        'icon',
        'active',
    ]);
});


it('Returns a 404 when single language is fetched with invalid Id', function () {
    $languageId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('languages/'.$languageId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});
