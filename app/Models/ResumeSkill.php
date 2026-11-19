<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeSkill extends BaseModel
{
    protected $table = 'resume_skills';

    protected $fillable = [
        'profile_id',
        'name',
        'category',
        'proficiency',
        'years_of_experience',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'years_of_experience' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ResumeProfile::class, 'profile_id');
    }
}
