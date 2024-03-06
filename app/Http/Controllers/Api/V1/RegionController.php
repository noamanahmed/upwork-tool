<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Models\Region;
use App\Services\RegionService;

class RegionController extends BaseController
{
    public function __construct(
        private RegionService $regionService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->regionService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->regionService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->regionService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRegionRequest $request)
    {
        return $this->regionService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        return $this->regionService->get($region->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRegionRequest $request, Region $region)
    {
        return $this->regionService->update($region->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Region $region)
    {
        return $this->regionService->delete($region->id);
    }
}
