<?php

namespace App\Models;


class JobSearch extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->belongsToMany(Job::class,'job_searches_jobs_pivot')->withPivot(['is_slack_webhook_sent']);
    }

    public function latestJobs()
    {
        return $this->jobs()->take(100)->latest();
    }
}
