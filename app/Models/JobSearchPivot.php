<?php

namespace App\Models;

class JobSearchPivot extends BaseModel
{
    public $table = 'job_searches_jobs_pivot';

    protected $fillable = [
        'job_search_id',
        'job_id',
        'is_slack_webhook_sent',
        'slack_sent_at',
        'slack_attempts',
        'slack_last_error',
    ];

    protected $casts = [
        'is_slack_webhook_sent' => 'boolean',
        'slack_sent_at' => 'datetime',
        'slack_attempts' => 'integer',
    ];
}
