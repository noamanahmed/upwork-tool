<?php


namespace App\Services;

use App\Enums\RoleStatusEnum;
use App\Models\Permission;
use App\Repositories\RoleRepository;
use App\Transformers\RoleCollectionTransformer;
use App\Transformers\RoleTransformer;
use Illuminate\Support\Facades\DB;

class RoleService extends BaseService{

    public function __construct(){
        $this->repository = new RoleRepository();
        $this->transformer = new RoleTransformer();
        $this->collectionTransformer = new RoleCollectionTransformer();
        $this->statusMapperEnum = RoleStatusEnum::class;

    }

    public function store($validatedRequestData)
    {
        DB::beginTransaction();
        $response = $this->repository->store($validatedRequestData);
        $role = $this->repository->getModel();
        $permissions = Permission::where('guard_name','web')->whereIn('name',$validatedRequestData['permissions'] ?? [])->get();
        $role->syncPermissions($permissions);
        DB::commit();
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($role);
        return $this->successfullApiResponse($transformer->toArray(),201);
    }

    public function update($modelId,$validatedRequestData)
    {
        DB::beginTransaction();
        $response = $this->repository->update($modelId,$validatedRequestData);
        $role = $this->repository->getModel();
        $permissions = Permission::where('guard_name','web')->whereIn('name',$validatedRequestData['permissions'] ?? [])->get();
        $role->syncPermissions($permissions);
        DB::commit();
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($role);
        return $this->successfullApiResponse($transformer->toArray(),201);
    }

}

