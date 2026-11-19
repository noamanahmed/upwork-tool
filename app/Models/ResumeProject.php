<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeProject extends BaseModel
{
    protected $table = 'resume_projects';

    protected $fillable = [
        'profile_id',
        'title',
        'client_name',
        'problem',
        'solution',
        'result',
        'tech_stack',
        'tags',
        'project_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
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
