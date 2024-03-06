<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreProxyRequest;
use App\Http\Requests\UpdateProxyRequest;
use App\Models\Proxy;
use App\Services\ProxyService;

class ProxyController extends BaseController
{
    public function __construct(
        private ProxyService $proxyService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->proxyService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->proxyService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->proxyService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProxyRequest $request)
    {
        return $this->proxyService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Proxy $proxy)
    {
        return $this->proxyService->get($proxy->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProxyRequest $request, Proxy $proxy)
    {
        return $this->proxyService->update($proxy->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proxy $proxy)
    {
        return $this->proxyService->delete($proxy->id);
    }
}
