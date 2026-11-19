<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeProfile extends BaseModel
{
    protected $table = 'resume_profiles';

    protected $fillable = [
        'first_name',
        'last_name',
        'title',
        'email',
        'phone',
        'city',
        'state',
        'country',
        'timezone',
        'experience_years',
        'summary',
        'github_url',
        'linkedin_url',
        'website_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'experience_years' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getLocationAttribute(): string
    {
        return implode(', ', array_filter([$this->city, $this->state, $this->country]));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(ResumeExperience::class, 'profile_id')->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(ResumeEducation::class, 'profile_id')->orderBy('sort_order');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(ResumeSkill::class, 'profile_id')->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(ResumeCertification::class, 'profile_id')->orderBy('sort_order');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(ResumeLanguage::class, 'profile_id')->orderBy('sort_order');
    }

    public function hobbies(): HasMany
    {
        return $this->hasMany(ResumeHobby::class, 'profile_id')->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ResumeProject::class, 'profile_id')->orderBy('sort_order');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(ResumeAchievement::class, 'profile_id')->orderBy('sort_order');
    }
}
