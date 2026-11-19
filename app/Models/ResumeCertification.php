<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeCertification extends BaseModel
{
    protected $table = 'resume_certifications';

    protected $fillable = [
        'profile_id',
        'name',
        'issuer',
        'issue_date',
        'expiry_date',
        'credential_id',
        'credential_url',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
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
