<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreUserSettingRequest;
use App\Http\Requests\UpdateUserSettingRequest;
use App\Models\UserSetting;
use App\Services\UserSettingService;

class UserSettingController extends BaseController
{
    public function __construct(
        private UserSettingService $usersettingService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->usersettingService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->usersettingService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->usersettingService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserSettingRequest $request)
    {
        return $this->usersettingService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(UserSetting $usersetting)
    {
        return $this->usersettingService->get($usersetting->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserSettingRequest $request, UserSetting $usersetting)
    {
        return $this->usersettingService->update($usersetting->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserSetting $usersetting)
    {
        return $this->usersettingService->delete($usersetting->id);
    }
}
