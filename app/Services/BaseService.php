<?php

namespace App\Services;

use App\Repositories\BaseRepositoryContract;
use App\Transformers\BaseTransformerContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BaseService implements BaseServiceContract{
    /**
     * The Repository to interact with
     *
     * @var Repository
     */
    protected BaseRepositoryContract $repository;

    /**
     * The transformer to transform API responses for single entity
     *
     * @var BaseTransformerContract
     */
    protected BaseTransformerContract $transformer;

    /**
     * The transformer to transform API responses for collections of single entity
     *
     * @var BaseTransformerContract
     */
    protected BaseTransformerContract $collectionTransformer;

    /**
     * Status mapper enum required for automatic mapping to pretty formats for status
     *
     * @var
     */
    protected $statusMapperEnum;

    public function __construct(
        protected Request $request
    ){}


    public function index(){
        $transformer = new $this->collectionTransformer();
        $transformer = $transformer->setResource($this->repository->index());
        return $this->successfullApiResponse($transformer->toArray());
    }
    public function dropdown(){
        return $this->successfullApiResponse($this->repository->dropdown());
    }
    public function dropdownForStatus(){
        return $this->successfullApiResponse(
            $this->statusMapperEnum::dropdown()
        );
    }
    public function delete($modelId){
        $this->repository->destory($modelId);
        return $this->apiResponse([],204);
    }
    public function store($validatedRequestData){
        $this->repository->store($validatedRequestData);
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($this->repository->getModel());
        return $this->successfullApiResponse($transformer->toArray(),201);
    }
    public function get($modelId){
        $model = $this->repository->find($modelId);
        if(is_null($model)) return $this->apiResponse(['message' => 'The entity was not found'],404);
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($model);
        return $this->successfullApiResponse($transformer->toArray(),201);
    }
    public function update($modelId,$validatedRequestData){
        $this->repository->update($modelId,$validatedRequestData);
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($this->repository->getModel());
        return $this->successfullApiResponse($transformer->toArray(),201);
    }
    public function destory($id)
    {
        $this->repository->destory($id);
        return $this->successfullApiResponse([],204);

    }
    public function destroyMulti($array)
    {
        $this->repository->destroyMulti($array);
        return $this->successfullApiResponse([],204);
    }

    public function apiResponse($data,$statusCode){
        if($statusCode === 204) return response(null,204);
        return response()->json($data,$statusCode);
    }
    public function successfullApiResponse(Array | Collection | EloquentCollection $data){
        return $this->apiResponse($data,200);
    }
    public function apiResponseWithValidationErrors($data){
        return $this->apiResponse($data,422);
    }
    public function apiResponseWithAuthenticationFailedError($data){
        return $this->apiResponse($data,401);
    }
    public function apiResponseWithAuthorizationFailedError($data){
        return $this->apiResponse($data,403);
    }

    public function convertObjectToArray($object)
    {
        return json_decode(json_encode($object),true);
    }
}
