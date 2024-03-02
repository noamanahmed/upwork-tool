<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Pest\TestSuite;

uses(
    Tests\TestCase::class,
    // Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeSuccessfulyJsonResponse', function ($response) {
    return $this->toBe(200)
    && expect($response->getContent())->toBeJson();
});
expect()->extend('toBePaginatorResponse', function () {
    $requiredKeys = ['current_page','next_page_url','path','per_page','prev_page_url','to','total'];
    foreach($requiredKeys as $key => $value)
    {
        if(!array_key_exists($key,$this->value)) return false;
    }
    return true;
});


/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getFactory()
{
    return new Tests\Factory();
}

function apiPrefix($url)
{
    return 'api/v1/'.$url;
}


beforeEach(function(){
    $this->artisan('migrate:fresh');
});

afterEach(function(){
    $this->artisan('db:wipe');
});


