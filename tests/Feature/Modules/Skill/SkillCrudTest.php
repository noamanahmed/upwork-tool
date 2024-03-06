<?php

use App\Models\Skill;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\artisan;



beforeEach(function(){
    $this->factory = getFactory();
    $this->factory->createUser();
    $this->factory->createSkill();
    $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->factory->getToken(),
    ]);

});


it('returns a paginated response of skills', function () {
    $response = $this->getJson(apiPrefix('skills'));
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

it('Returns an array of skills with there name when fetching dropdown', function () {
    $response = $this->getJson(apiPrefix('skills/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});


it('Returns an array of skill status', function () {
    $skillId = $this->factory::$skill->id;
    $response = $this->getJson(apiPrefix('skills/status/dropdown'));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
});

it('Returns a single skill with valid Id', function () {
    $skillId = $this->factory::$skill->id;
    $response = $this->getJson(apiPrefix('skills/'.$skillId));
    expect($response->status())->toBe(200);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys([
        'name',
        'description',
    ]);
});


it('Returns a 404 when single skill is fetched with invalid Id', function () {
    $skillId = rand(999999,9999999);
    $response = $this->getJson(apiPrefix('skills/'.$skillId));
    expect($response->status())->toBe(404);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['error']);
});


it('Returns a 422 when single skill is being created with incorrect data', function () {
    $response = $this->postJson(apiPrefix('skills'));
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});


it('Returns a 422 when single skill is being updated with incorrect data', function () {
    $skillId = $this->factory::$skill->id;
    $response = $this->patchJson(apiPrefix('skills/'.$skillId),[]);
    expect($response->status())->toBe(422);
    expect($response->getContent())
    ->toBeJson();
    expect($response->json())
    ->toHaveKeys(['errors','message']);
});



it('Returns a 200 when single skill is created', function () {
    $this->factory->makeSkill();
    $response = $this->postJson(apiPrefix('skills'),$this->factory::$skill->toArray());
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


it('Returns a 200 when single skill is updated', function () {
    $oldSkill = $this->factory::$skill;
    $oldSkillId = $oldSkill->id;
    $this->factory->makeSkill();
    $response = $this->patchJson(apiPrefix('skills/'.$oldSkillId),$this->factory::$skill->toArray());
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
    ->name->not->toBe($oldSkill->name);

    expect($response->json())
    ->not->toHaveKeys(['errors','message']);
});

it('Returns a 204 when single skill is delete', function () {
    $skillId = $this->factory::$skill->id;
    $response = $this->deleteJson(apiPrefix('skills/'.$skillId));
    expect($response->status())->toBe(204);
    expect($response->getContent())
    ->toBe('');

    expect(Skill::find($skillId))
    ->toBeNull();
});
