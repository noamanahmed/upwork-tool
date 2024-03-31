<?php

namespace App\Models;


class Job extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    function getSlackNotificationMessageAttribute()
    {
        $job = $this;
        $text = '';
        $text .= '<!channel>';
        $text .= "\n\n";
        $text .= ' *Job Title* '.$job->title;
        $text .= "\n\n";
        $text .= ' *Job Description* '.$job->description;
        $text .= "\n\n";
        $text .= '*Job Link*  :' .'https://www.upwork.com/jobs/'.$job->ciphertext;
        return $text;
    }
}
