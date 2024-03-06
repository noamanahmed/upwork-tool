<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreUpworkAccountRequest;
use App\Http\Requests\UpdateUpworkAccountRequest;
use App\Models\UpworkAccount;
use App\Services\UpworkAccountService;

class UpworkAccountController extends BaseController
{
    public function __construct(
        private UpworkAccountService $upworkaccountService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->upworkaccountService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->upworkaccountService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->upworkaccountService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpworkAccountRequest $request)
    {
        return $this->upworkaccountService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(UpworkAccount $upworkaccount)
    {
        return $this->upworkaccountService->get($upworkaccount->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUpworkAccountRequest $request, UpworkAccount $upworkaccount)
    {
        return $this->upworkaccountService->update($upworkaccount->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UpworkAccount $upworkaccount)
    {
        return $this->upworkaccountService->delete($upworkaccount->id);
    }
}
