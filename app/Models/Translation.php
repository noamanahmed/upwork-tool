<?php

namespace App\Models;

class Translation extends BaseModel
{
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
