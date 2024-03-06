<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use App\Services\JobService;

class JobController extends BaseController
{
    public function __construct(
        private JobService $jobService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->jobService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->jobService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->jobService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobRequest $request)
    {
        return $this->jobService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job)
    {
        return $this->jobService->get($job->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobRequest $request, Job $job)
    {
        return $this->jobService->update($job->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        return $this->jobService->delete($job->id);
    }
}
