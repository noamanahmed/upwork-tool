<?php

namespace App\Models;


class RssJobs extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class,'ciphertext','ciphertext');
    }
}
