<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeExperience extends BaseModel
{
    protected $table = 'resume_experiences';

    protected $fillable = [
        'profile_id',
        'company',
        'role',
        'location',
        'employment_type',
        'start_date',
        'end_date',
        'is_current',
        'highlights',
        'technologies',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
        'highlights' => 'array',
        'technologies' => 'array',
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
