<?php

namespace App\Models;


class Proposal extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];
    public function job()
    {
        return $this->belongsTo(Job::class,'job_id','id');
    }

}
