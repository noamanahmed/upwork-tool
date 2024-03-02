<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Models\Skill;
use App\Services\SkillService;

class SkillController extends BaseController
{
    public function __construct(
        private SkillService $skillService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->skillService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->skillService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->skillService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSkillRequest $request)
    {
        return $this->skillService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill)
    {
        return $this->skillService->get($skill->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSkillRequest $request, Skill $skill)
    {
        return $this->skillService->update($skill->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill)
    {
        return $this->skillService->delete($skill->id);
    }
}
