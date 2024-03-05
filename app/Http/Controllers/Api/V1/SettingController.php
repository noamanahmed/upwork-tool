<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;

class SettingController extends BaseController
{
    public function __construct(
        private SettingService $settingService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->settingService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->settingService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->settingService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSettingRequest $request)
    {
        return $this->settingService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        return $this->settingService->get($setting->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        return $this->settingService->update($setting->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        return $this->settingService->delete($setting->id);
    }
}
