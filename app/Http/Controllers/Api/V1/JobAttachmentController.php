<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreJobAttachmentRequest;
use App\Http\Requests\UpdateJobAttachmentRequest;
use App\Models\JobAttachment;
use App\Services\JobAttachmentService;

class JobAttachmentController extends BaseController
{
    public function __construct(
        private JobAttachmentService $jobattachmentService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->jobattachmentService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->jobattachmentService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->jobattachmentService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobAttachmentRequest $request)
    {
        return $this->jobattachmentService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(JobAttachment $jobattachment)
    {
        return $this->jobattachmentService->get($jobattachment->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobAttachmentRequest $request, JobAttachment $jobattachment)
    {
        return $this->jobattachmentService->update($jobattachment->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobAttachment $jobattachment)
    {
        return $this->jobattachmentService->delete($jobattachment->id);
    }
}
