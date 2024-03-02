<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\UpdateProfile;
use App\Http\Requests\UpdateSettings;
use App\Services\AccountService;
use App\Services\UserSettingService;

class AccountController extends BaseController
{
    public function __construct(
        private AccountService $accountService,
        private UserSettingService $userSettingService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function profile()
    {
        return $this->accountService->profile();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function updateProfile(UpdateProfile $request)
    {
        return $this->accountService->updateProfile($request->validated());
    }

    /**
     * Display a listing of the resource.
     */
    public function settings()
    {
        return $this->userSettingService->settings();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function updateSettings(UpdateSettings $request)
    {
        return $this->userSettingService->updateSettings($request->validated());
    }

}
