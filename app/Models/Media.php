<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends BaseModel
{
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
    public function getDownloadUrlAttribute()
    {

        if(!$this->supportsTemporaryUrl()) return $this->url;
        return Storage::temporaryUrl($this->path, now()->addMinutes(10));
    }

    public function getSignedUrlAttribute()
    {
        if(!$this->supportsTemporaryUrl()) return $this->url;
        return Storage::temporaryUrl($this->path, now()->addMinutes(10));
    }

    public function supportsTemporaryUrl()
    {
        return !method_exists(Storage::disk(config('filesystems.default')),'temporaryUrl');
    }
}
