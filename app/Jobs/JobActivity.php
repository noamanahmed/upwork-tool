<?php

namespace App\Jobs;

use App\Models\Job;
use App\Models\JobActivity as ModelsJobActivity;
use App\Repositories\JobSearchRepository;
use App\Services\CategoryService;
use App\Services\JobActivityService;
use App\Services\JobSearchService;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class JobActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Job $upworkJob,public int $delayInSeconds)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $upworkJob = $this->upworkJob;
        $lock = Cache::lock('job_activity_service_update_job_activity_' . $upworkJob->id. '_schedule_' . $this->delayInSeconds, 60);
        if(!$lock->get()) return;
        $activtyAlreadyExists = ModelsJobActivity::where('job_id',$upworkJob->id)->where('schedule',$this->delayInSeconds)->count();
        if($activtyAlreadyExists > 0) return;
        $activities = app(UpWorkService::class)->jobActivity($this->upworkJob->toArray());
        app(JobActivityService::class)->updatejobActivities($this->upworkJob,$activities,$this->delayInSeconds);
        $lock->release();
    }
}
