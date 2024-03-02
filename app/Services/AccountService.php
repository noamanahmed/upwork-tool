<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Transformers\ProfileTransformer;
use Illuminate\Support\Facades\Auth;

class AccountService extends BaseService implements AccountServiceContract{

    public function __construct(){
        $this->repository = new UserRepository();
        $this->transformer = new ProfileTransformer();
    }

    public function profile(){
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource(Auth::user());
        return $this->successfullApiResponse($transformer->toArray());
    }

    public function updateProfile($validatedRequest){
        $transformer = new $this->transformer();
        $this->repository->update(Auth::user()->id,$validatedRequest);
        $transformer = $transformer->setResource($this->repository->getModel());
        return $this->successfullApiResponse($transformer->toArray());
    }
}
