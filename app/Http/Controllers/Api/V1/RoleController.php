<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;

class RoleController extends BaseController
{
    public function __construct(
        private RoleService $roleService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->roleService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->roleService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->roleService->dropdownForStatus();
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        return $this->roleService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $this->roleService->get($role->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        return $this->roleService->update($role->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        return $this->roleService->delete($role->id);
    }
}
