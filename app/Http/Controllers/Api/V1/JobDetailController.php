<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreJobDetailRequest;
use App\Http\Requests\UpdateJobDetailRequest;
use App\Models\JobDetail;
use App\Services\JobDetailService;

class JobDetailController extends BaseController
{
    public function __construct(
        private JobDetailService $jobdetailService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->jobdetailService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->jobdetailService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->jobdetailService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobDetailRequest $request)
    {
        return $this->jobdetailService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(JobDetail $jobdetail)
    {
        return $this->jobdetailService->get($jobdetail->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobDetailRequest $request, JobDetail $jobdetail)
    {
        return $this->jobdetailService->update($jobdetail->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobDetail $jobdetail)
    {
        return $this->jobdetailService->delete($jobdetail->id);
    }
}
