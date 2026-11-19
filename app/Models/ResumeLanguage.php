<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeLanguage extends BaseModel
{
    protected $table = 'resume_languages';

    protected $fillable = [
        'profile_id',
        'name',
        'proficiency',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ResumeProfile::class, 'profile_id');
    }
}
