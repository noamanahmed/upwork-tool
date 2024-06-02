<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreUpWorkRequest;
use App\Http\Requests\UpdateUpWorkRequest;
use App\Models\Job;
use App\Models\JobSearch;
use App\Models\RssJobSearches;
use App\Models\UpWork;
use App\Services\JobService;
use App\Services\UpWorkService;
use Request;

class UpWorkController extends BaseController
{
    public function __construct(
        private UpWorkService $upworkService,
        private JobService $jobService
    ){}

    public function init(Request $request)
    {
        return $this->upworkService->init();
    }
    public function code(Request $request)
    {
        return $this->upworkService->code();
    }
    public function jobs(JobSearch $jobSearch,Request $request)
    {
        return $this->upworkService->jobs($jobSearch->toArray());
    }
    public function rssJobs(RssJobSearches $jobSearch,Request $request)
    {
        return $this->upworkService->rssJobs($jobSearch);
    }

    public function job($jobId, Request $request)
    {
        return $this->jobService->get($jobId);
    }
    public function jobSlackMessage($jobId, Request $request)
    {
        $job = Job::findOrfail($jobId);
        return $job->slack_notification_message;
    }

    public function categories(Request $request)
    {
        return $this->upworkService->categories();
    }

    public function skills(Request $request)
    {
        return $this->upworkService->skills();
    }
    public function timezones(Request $request)
    {
        return $this->upworkService->timezones();
    }
    public function languages(Request $request)
    {
        return $this->upworkService->languages();
    }
    public function countries(Request $request)
    {
        return $this->upworkService->countries();
    }
    public function regions(Request $request)
    {
        return $this->upworkService->regions();
    }
    public function analytics(Request $request)
    {
        return $this->upworkService->analytics();
    }
}
