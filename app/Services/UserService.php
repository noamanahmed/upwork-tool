<?php


namespace App\Services;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Transformers\UserCollectionTransformer;
use App\Transformers\UserTransformer;
use DB;

class UserService extends BaseService{

    public function __construct(){
        $this->repository = new UserRepository();
        $this->transformer = new UserTransformer();
        $this->collectionTransformer = new UserCollectionTransformer();
        $this->statusMapperEnum = UserStatusEnum::class;

    }
    public function store($validatedRequest)
    {
        // For new rows rollback logic can be omitted.
        DB::beginTransaction();
        $role = app(RoleRepository::class)->find($validatedRequest['role']);
        $this->repository->store($validatedRequest);
        $user = $this->repository->getModel();
        if(
            $validatedRequest['status'] === UserStatusEnum::ACTIVE->value &&
            !$user->hasVerifiedEmail()
        )
        {
            $user->email_verified_at = now();
            $user->save();
        }

        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($user);
        $user = $this->repository->getModel();
        $user->assignRole($role);
        $user->refresh();
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($user);
        DB::commit();
        return $this->successfullApiResponse($transformer->toArray(),201);
    }
    public function update($modelId,$validatedRequestData){
        $this->repository->update($modelId,$validatedRequestData);
        $user = $this->repository->getModel();
        if(
            $validatedRequestData['status'] === UserStatusEnum::ACTIVE->value &&
            !$user->hasVerifiedEmail()
        )
        {
            $user->email_verified_at = now();
            $user->save();
        }

        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($user);
        return $this->successfullApiResponse($transformer->toArray(),201);
    }


    public function dropdownForType()
    {
        return $this->successfullApiResponse(
            UserTypeEnum::dropdown()
        );
    }
}

