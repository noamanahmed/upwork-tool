<?php

namespace App\Models;


class JobActivity extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function dispatchSchedule()
    {
        defined('MINUTE_IN_SECONDS') || define('MINUTE_IN_SECONDS',60);
        return [
            1 * MINUTE_IN_SECONDS,
            2 * MINUTE_IN_SECONDS,
            3 * MINUTE_IN_SECONDS,
            4 * MINUTE_IN_SECONDS,
            5 * MINUTE_IN_SECONDS,
            6 * MINUTE_IN_SECONDS,
            7 * MINUTE_IN_SECONDS,
            8 * MINUTE_IN_SECONDS,
            9 * MINUTE_IN_SECONDS,
            10 * MINUTE_IN_SECONDS,
            15 * MINUTE_IN_SECONDS,
            20 * MINUTE_IN_SECONDS,
            25 * MINUTE_IN_SECONDS,
            30 * MINUTE_IN_SECONDS,
        ];
    }
}
