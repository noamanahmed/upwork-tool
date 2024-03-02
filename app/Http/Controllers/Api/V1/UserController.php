<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;

class UserController extends BaseController
{
    public function __construct(
        private UserService $userService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->userService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->userService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->userService->dropdownForStatus();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForType()
    {
        return $this->userService->dropdownForType();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        return $this->userService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->userService->get($user->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        return $this->userService->update($user->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        return $this->userService->delete($user->id);
    }
}
