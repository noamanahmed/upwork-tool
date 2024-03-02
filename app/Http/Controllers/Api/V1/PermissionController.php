<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\PermissionService;

class PermissionController extends BaseController
{
    public function __construct(
        private PermissionService $permissionService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->permissionService->index();
    }
}
