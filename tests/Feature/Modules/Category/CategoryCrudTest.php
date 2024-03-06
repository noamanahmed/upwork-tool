<?php

use App\Models\Category;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createCategory();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of categorys', function () {
    $response = $this->getJson(apiPrefix('categorys'));
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

it('Returns an array of categorys with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('categorys/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of category status', function () {
    $categoryId = $this->factory::$category->id;
    $response = $this->getJson(apiPrefix('categorys/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single category with valid Id', function () {
    $categoryId = $this->factory::$category->id;
    $response = $this->getJson(apiPrefix('categorys/'.$categoryId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single category is fetched with invalid Id', function () {
    $categoryId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('categorys/'.$categoryId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single category is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('categorys'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single category is being updated with incorrect data', function () {
    $categoryId = $this->factory::$category->id;
    $response = $this->patchJson(apiPrefix('categorys/'.$categoryId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single category is created', function () {
    $this->factory->makeCategory();
    $response = $this->postJson(apiPrefix('categorys'),$this->factory::$category->toArray());
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


it('Returns a 200 when single category is updated', function () {
    $oldCategory = $this->factory::$category;
    $oldCategoryId = $oldCategory->id;
    $this->factory->makeCategory();
    $response = $this->patchJson(apiPrefix('categorys/'.$oldCategoryId),$this->factory::$category->toArray());
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
    ->name->not->toBe($oldCategory->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single category is delete', function () {
    $categoryId = $this->factory::$category->id;
    $response = $this->deleteJson(apiPrefix('categorys/'.$categoryId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Category::find($categoryId))
    ->toBeNull();
});
