<?php

namespace App\Models;


class RssJobSearches extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function jobs()
    {
        return $this->hasMany(RssJobs::class,'rss_job_search_id');
    }
    public function latestJobs()
    {
        return $this->jobs()->take(100)->latest();
    }

}
