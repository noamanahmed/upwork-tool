<?php

namespace App\Services;

use App\Repositories\UserSettingRepository;
use App\Transformers\UserSettingCollectionTransformer;
use App\Transformers\UserSettingTransformer;
use Auth;
use Illuminate\Support\Facades\Hash;

class UserSettingService extends BaseService implements UserSettingServiceContract{

    public function __construct(){
        $this->repository = new UserSettingRepository();
        $this->transformer = new UserSettingTransformer();
        $this->collectionTransformer = new UserSettingCollectionTransformer();
    }
    public function settings(){
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource(Auth::user()->settings);
        return $this->successfullApiResponse($transformer->toArray());
    }

    public function updateSettings($validatedRequest){
        $transformer = new $this->transformer();
        $this->repository->update(Auth::user()->settings->id,$validatedRequest);

        if(
            array_key_exists('password',$validatedRequest) &&
            array_key_exists('old_password',$validatedRequest)
        )
        {
            $password = $validatedRequest['password'];
            $oldPassword = $validatedRequest['old_password'];
            if (! Hash::check($oldPassword, auth()->user()->password)) {
                return $this->apiResponseWithValidationErrors([
                    'message' => 'The provided password is incorrect.',
                    'errors' => [
                        'old_password' => 'The provided password is incorrect.'
                    ],
                ]);
            }
            auth()->user()->password = Hash::make($password);
            auth()->user()->save();
        }
        $transformer = $transformer->setResource($this->repository->getModel());
        return $this->successfullApiResponse($transformer->toArray());
    }

    public function createDefaultSettings($user)
    {
        $this->repository->createDefaultSettings($user);
    }

}
