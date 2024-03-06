<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreJobSearchRequest;
use App\Http\Requests\UpdateJobSearchRequest;
use App\Models\JobSearch;
use App\Services\JobSearchService;

class JobSearchController extends BaseController
{
    public function __construct(
        private JobSearchService $jobsearchService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->jobsearchService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->jobsearchService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->jobsearchService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobSearchRequest $request)
    {
        return $this->jobsearchService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(JobSearch $jobsearch)
    {
        return $this->jobsearchService->get($jobsearch->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobSearchRequest $request, JobSearch $jobsearch)
    {
        return $this->jobsearchService->update($jobsearch->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobSearch $jobsearch)
    {
        return $this->jobsearchService->delete($jobsearch->id);
    }
}
