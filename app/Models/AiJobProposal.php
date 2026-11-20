<?php

namespace App\Models;

use App\Services\Template\MustacheEngine;
use Illuminate\Support\Str;

class AiJobProposal extends BaseModel
{
    protected $casts = [
        'generated_at' => 'datetime',
        'queued_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'job_id',
        'proposal',
        'status',
        'provider',
        'model',
        'conversation_id',
        'description',
        'prompt',
        'instructions',
        'attempts',
        'last_error',
        'queued_at',
        'generated_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Public API
    |--------------------------------------------------------------------------
    */
    public function getPromptText(?string $provider = null): string
    {
        $provider = $provider ?? $this->provider;
        $template = null;

        if ($provider) {
            $template = Setting::get('AI_PROMPT_TEMPLATE_' . strtoupper($provider));
        }

        if (empty($template)) {
            $template = Setting::get('AI_PROMPT_TEMPLATE');
        }

        if (empty($template)) {
            $template = '';
        }

        $context = $this->buildTemplateContext($provider);

        return MustacheEngine::render($template, $context);
    }

    public function getModelInstructions(?string $provider = null): string
    {
        $provider = $provider ?? $this->provider;
        $template = null;

        if ($provider) {
            $template = Setting::get('AI_SYSTEM_PROMPT_' . strtoupper($provider));
        }

        if (empty($template)) {
            $template = Setting::get('AI_SYSTEM_PROMPT');
        }

        if (empty($template)) {
            $template = '';
        }

        $context = $this->buildTemplateContext($provider);

        return MustacheEngine::render($template, $context);
    }

    /**
     * Build rich template context dictionary for Mustache rendering.
     */
    public function buildTemplateContext(?string $provider = null): array
    {
        $job = $this->job ?? ($this->job_id ? Job::find($this->job_id) : null);
        $jobDetails = $this->getJobDetails();
        $freelancerContext = $this->buildContext();
        $insightsText = $this->extractJobInsights();
        $insightsData = $this->extractJobInsightsData();

        $additionalData = $job ? $job->getAdditonalData() : [];

        $budgetStr = 'N/A';
        if ($job) {
            if ($job->budget_minimum && $job->budget_maximum) {
                $budgetStr = ($job->budget_minimum === $job->budget_maximum)
                    ? '$' . number_format($job->budget_minimum)
                    : '$' . number_format($job->budget_minimum) . ' - $' . number_format($job->budget_maximum);
            } elseif ($job->budget_minimum) {
                $budgetStr = '$' . number_format($job->budget_minimum);
            }
        }

        $clientName = $additionalData['node.job.ownership.team.name'] ?? 'N/A';
        $clientTotalSpend = isset($additionalData['node.client.totalSpent.rawValue'])
            ? '$' . number_format((float) $additionalData['node.client.totalSpent.rawValue'])
            : ($job && $job->client_total_spent ? '$' . number_format((float) $job->client_total_spent) : 'N/A');

        $skillsList = $this->getRawMatchedSkills();
        $achievementsList = $this->getRawMatchedAchievements();
        $caseStudiesList = $this->getRawMatchedCaseStudies();
        $experiencesList = $this->getRawMatchedExperiences();
        $educationsList = $this->getRawEducations();
        $certificationsList = $this->getRawCertifications();
        $languagesList = $this->getRawLanguages();
        $hobbiesList = $this->getRawHobbies();

        $resolvedProvider = $provider ?? $this->provider ?? 'default';

        return [
            'job' => [
                'id' => $job?->id,
                'title' => $jobDetails['title'],
                'description' => $jobDetails['description'],
                'budget' => $budgetStr !== 'N/A' ? $budgetStr : '',
                'budget_display' => $budgetStr,
                'budget_min' => $job?->budget_minimum,
                'budget_max' => $job?->budget_maximum,
                'type' => $job?->is_hourly ? 'HOURLY' : 'FIXED RATE',
                'is_hourly' => (bool) $job?->is_hourly,
                'hourly' => (bool) $job?->is_hourly,
                'location' => $job?->location ?? 'N/A',
                'total_applicants' => $additionalData['node.totalApplicants'] ?? 'N/A',
                'upwork_url' => $job ? $job->getUpworkUrl() : '',
                'posted_at' => $job?->posted_at?->diffForHumans() ?? 'N/A',
                'skills' => ($job && $job->relationLoaded('skills')) ? $job->skills->pluck('name')->implode(', ') : '',
            ],
            'client' => [
                'name' => $clientName,
                'location' => $job?->location ?? 'N/A',
                'country' => $additionalData['node.client.location.country'] ?? 'N/A',
                'city' => $additionalData['node.client.location.city'] ?? 'N/A',
                'total_spent' => $clientTotalSpend,
                'total_hires' => $job?->client_total_hires ?? ($additionalData['node.client.totalHires'] ?? 'N/A'),
                'total_reviews' => $job?->client_total_reviews ?? ($additionalData['node.client.totalReviews'] ?? 'N/A'),
                'feedback' => $job?->client_total_feedback ? number_format($job->client_total_feedback, 1) : ($additionalData['node.client.totalFeedback'] ?? 'N/A'),
                'total_posted_jobs' => $job?->client_total_posted_jobs ?? ($additionalData['node.client.totalPostedJobs'] ?? 'N/A'),
                'payment_verified' => $job?->is_payment_verified ? 'Yes' : 'No',
                'is_payment_verified' => (bool) $job?->is_payment_verified,
            ],
            'freelancer' => [
                'name' => $freelancerContext['name'] ?? '',
                'first_name' => $freelancerContext['first_name'] ?? '',
                'last_name' => $freelancerContext['last_name'] ?? '',
                'title' => $freelancerContext['title'] ?? '',
                'summary' => $freelancerContext['summary'] ?? '',
                'city' => $freelancerContext['city'] ?? '',
                'country' => $freelancerContext['country'] ?? '',
                'skills' => $freelancerContext['skills'],
                'skills_list' => $skillsList,
                'achievements' => $freelancerContext['achievements'],
                'achievements_list' => $achievementsList,
                'experience' => $freelancerContext['experience'],
                'experiences' => $experiencesList,
                'education' => $freelancerContext['education'] ?? '',
                'educations' => $educationsList,
                'certifications' => $freelancerContext['certifications'],
                'certifications_list' => $certificationsList,
                'case_study' => $freelancerContext['case_study'],
                'case_studies' => $caseStudiesList,
                'languages' => $freelancerContext['languages'] ?? '',
                'languages_list' => $languagesList,
                'hobbies' => $freelancerContext['hobbies'] ?? '',
                'hobbies_list' => $hobbiesList,
            ],
            'insights' => $insightsText,
            'insights_data' => $insightsData,
            'provider' => [
                'name' => $resolvedProvider,
                'model' => $this->model ?? config("services.ai.{$resolvedProvider}.model", ''),
            ],

            // Direct convenience aliases
            'job_title' => $jobDetails['title'],
            'job_description' => $jobDetails['description'],
            'job_budget' => $budgetStr !== 'N/A' ? $budgetStr : '',
            'client_name' => $clientName,
            'freelancer_name' => $freelancerContext['name'],
            'freelancer_skills' => $freelancerContext['skills'],
            'freelancer_achievements' => $freelancerContext['achievements'],
            'freelancer_experience' => $freelancerContext['experience'],
            'freelancer_case_study' => $freelancerContext['case_study'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Core Context Builder
    |--------------------------------------------------------------------------
    */
    protected function buildContext(): array
    {
        $profile = $this->getActiveResumeProfile();

        if ($profile) {
            return $this->buildContextFromProfile($profile);
        }

        return [
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'title' => '',
            'city' => '',
            'country' => '',
            'summary' => '',
            'skills' => '',
            'achievements' => '',
            'experience' => '',
            'education' => '',
            'certifications' => '',
            'languages' => '',
            'hobbies' => '',
            'case_study' => '',
        ];
    }

    /**
     * Get active ResumeProfile from database.
     */
    protected function getActiveResumeProfile(): ?ResumeProfile
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('resume_profiles')) {
                return ResumeProfile::active()->with([
                    'skills' => fn ($q) => $q->active(),
                    'experiences' => fn ($q) => $q->active(),
                    'educations' => fn ($q) => $q->active(),
                    'certifications' => fn ($q) => $q->active(),
                    'languages' => fn ($q) => $q->active(),
                    'hobbies' => fn ($q) => $q->active(),
                    'projects' => fn ($q) => $q->active(),
                    'achievements' => fn ($q) => $q->active(),
                ])->first();
            }
        } catch (\Throwable $e) {
            // DB not migrated yet or table missing
        }

        return null;
    }

    /**
     * Build context from active ResumeProfile.
     */
    protected function buildContextFromProfile(ResumeProfile $profile): array
    {
        $jobText = $this->normalizeKeywords(
            strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
        );

        // 1. Relevant Skills
        $matchedSkills = [];
        foreach ($profile->skills as $skill) {
            if (Str::contains($jobText, Str::lower($skill->name))) {
                $matchedSkills[] = $skill->name;
            }
        }
        if (empty($matchedSkills)) {
            $matchedSkills = $profile->skills->where('is_featured', true)->pluck('name')->all();
            if (empty($matchedSkills)) {
                $matchedSkills = $profile->skills->take(5)->pluck('name')->all();
            }
        }

        // 2. Achievements
        $matchedAchievements = [];
        foreach ($profile->achievements as $ach) {
            if (Str::contains($jobText, Str::lower($ach->category))) {
                $matchedAchievements[] = $ach->title;
            }
        }
        if (empty($matchedAchievements)) {
            $general = $profile->achievements->where('category', 'general')->pluck('title')->all();
            $matchedAchievements = !empty($general) ? [$general[0]] : ($profile->achievements->first() ? [$profile->achievements->first()->title] : []);
        }

        // 3. Experience
        $expText = '';
        $matchedExp = $profile->experiences->first();
        foreach ($profile->experiences as $exp) {
            $expCombined = Str::lower($exp->role . ' ' . $exp->company . ' ' . json_encode($exp->highlights));
            if (Str::contains($jobText, ['laravel', 'vue', 'fullstack', 'backend', 'api']) && Str::contains($expCombined, ['laravel', 'vue', 'api'])) {
                $matchedExp = $exp;
                break;
            }
        }
        if ($matchedExp) {
            $period = $matchedExp->is_current ? 'Present' : ($matchedExp->end_date ? $matchedExp->end_date->format('Y') : '');
            $start = $matchedExp->start_date ? $matchedExp->start_date->format('Y') : '';
            $expText = "{$matchedExp->role} at {$matchedExp->company} ({$start} - {$period})";
        }

        // 4. Certifications
        $certText = '';
        if ($profile->certifications->isNotEmpty()) {
            $firstCert = $profile->certifications->first();
            $certYear = $firstCert->issue_date ? $firstCert->issue_date->format('Y') : '';
            $certText = "{$firstCert->name} ({$firstCert->issuer}" . ($certYear ? ", {$certYear}" : "") . ")";
        }

        // 5. Case Study / Project
        $matchedProject = null;
        foreach ($profile->projects as $project) {
            foreach ((array) ($project->tags ?? []) as $tag) {
                if (Str::contains($jobText, Str::lower($tag))) {
                    $matchedProject = $project;
                    break 2;
                }
            }
        }
        if (!$matchedProject && $profile->projects->isNotEmpty()) {
            $matchedProject = $profile->projects->first();
        }

        $caseStudyText = $matchedProject
            ? "Relevant Past Work:\n- {$matchedProject->title} → {$matchedProject->result}"
            : '';

        // 6. Education
        $eduText = '';
        if ($profile->educations->isNotEmpty()) {
            $edu = $profile->educations->first();
            $eduText = "{$edu->degree}" . ($edu->field_of_study ? " in {$edu->field_of_study}" : "") . " - {$edu->institution}";
        }

        // 7. Languages
        $langText = $profile->languages->map(fn ($l) => "{$l->name} ({$l->proficiency})")->implode(', ');

        // 8. Hobbies
        $hobbyText = $profile->hobbies->pluck('name')->implode(', ');

        return [
            'name' => $profile->full_name,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'title' => $profile->title ?? '',
            'city' => $profile->city ?? '',
            'country' => $profile->country ?? '',
            'summary' => $profile->summary ?? '',
            'skills' => implode(', ', array_unique($matchedSkills)),
            'achievements' => implode("\n", array_unique($matchedAchievements)),
            'experience' => $expText,
            'education' => $eduText,
            'certifications' => $certText,
            'languages' => $langText,
            'hobbies' => $hobbyText,
            'case_study' => $caseStudyText,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Keyword Normalization
    |--------------------------------------------------------------------------
    */
    protected function normalizeKeywords(string $text): string
    {
        $map = [
            'api' => ['api', 'rest', 'integration'],
            'frontend' => ['frontend', 'ui', 'ux'],
            'backend' => ['backend', 'server', 'database'],
            'performance' => ['slow', 'optimize', 'performance'],
            'bug' => ['bug', 'fix', 'issue', 'error'],
        ];

        foreach ($map as $key => $variants) {
            foreach ($variants as $variant) {
                if (Str::contains($text, $variant)) {
                    $text .= ' ' . $key;
                }
            }
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | Selection Logic
    |--------------------------------------------------------------------------
    */
    public function getRawMatchedSkills(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->skills->isNotEmpty()) {
            $jobText = $this->normalizeKeywords(
                strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
            );
            $matched = [];
            foreach ($profile->skills as $skill) {
                if (Str::contains($jobText, Str::lower($skill->name))) {
                    $matched[] = $skill->name;
                }
            }
            if (!empty($matched)) {
                return array_values(array_unique($matched));
            }
            $featured = $profile->skills->where('is_featured', true)->pluck('name')->all();
            if (!empty($featured)) {
                return array_values($featured);
            }
            return $profile->skills->take(5)->pluck('name')->all();
        }

        return [];
    }

    public function getRawMatchedAchievements(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->achievements->isNotEmpty()) {
            $jobText = $this->normalizeKeywords(
                strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
            );
            $matched = [];
            foreach ($profile->achievements as $ach) {
                if (Str::contains($jobText, Str::lower($ach->category))) {
                    $matched[] = $ach->title;
                }
            }
            if (!empty($matched)) {
                return array_values(array_unique($matched));
            }
            $general = $profile->achievements->where('category', 'general')->pluck('title')->all();
            return !empty($general) ? [$general[0]] : [$profile->achievements->first()->title];
        }

        return [];
    }

    public function getRawMatchedCaseStudies(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->projects->isNotEmpty()) {
            $jobText = $this->normalizeKeywords(
                strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
            );
            $matched = [];
            foreach ($profile->projects as $proj) {
                foreach ((array) ($proj->tags ?? []) as $tag) {
                    if (Str::contains($jobText, Str::lower($tag))) {
                        $matched[] = [
                            'title' => $proj->title,
                            'problem' => $proj->problem,
                            'solution' => $proj->solution,
                            'result' => $proj->result,
                            'tech_stack' => $proj->tech_stack,
                            'tags' => $proj->tags,
                        ];
                        break;
                    }
                }
            }
            if (!empty($matched)) {
                return array_slice($matched, 0, 3);
            }
            $first = $profile->projects->first();
            return [[
                'title' => $first->title,
                'problem' => $first->problem,
                'solution' => $first->solution,
                'result' => $first->result,
                'tech_stack' => $first->tech_stack,
                'tags' => $first->tags,
            ]];
        }

        return [];
    }

    public function getRawMatchedExperiences(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->experiences->isNotEmpty()) {
            return $profile->experiences->map(function ($exp) {
                return [
                    'company' => $exp->company,
                    'role' => $exp->role,
                    'location' => $exp->location,
                    'employment_type' => $exp->employment_type,
                    'start_date' => $exp->start_date?->format('Y-m'),
                    'end_date' => $exp->is_current ? 'Present' : $exp->end_date?->format('Y-m'),
                    'is_current' => $exp->is_current,
                    'highlights' => $exp->highlights ?? [],
                    'technologies' => $exp->technologies ?? [],
                ];
            })->all();
        }

        return [];
    }

    public function getRawEducations(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->educations->isNotEmpty()) {
            return $profile->educations->map(function ($edu) {
                return [
                    'institution' => $edu->institution,
                    'degree' => $edu->degree,
                    'field_of_study' => $edu->field_of_study,
                    'start_date' => $edu->start_date?->format('Y'),
                    'end_date' => $edu->is_current ? 'Present' : $edu->end_date?->format('Y'),
                    'grade' => $edu->grade,
                    'description' => $edu->description,
                ];
            })->all();
        }

        return [];
    }

    public function getRawCertifications(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->certifications->isNotEmpty()) {
            return $profile->certifications->map(function ($cert) {
                return [
                    'name' => $cert->name,
                    'issuer' => $cert->issuer,
                    'issue_date' => $cert->issue_date?->format('Y'),
                    'credential_url' => $cert->credential_url,
                ];
            })->all();
        }

        return [];
    }

    public function getRawLanguages(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->languages->isNotEmpty()) {
            return $profile->languages->map(function ($lang) {
                return [
                    'name' => $lang->name,
                    'proficiency' => $lang->proficiency,
                ];
            })->all();
        }

        return [];
    }

    public function getRawHobbies(): array
    {
        $profile = $this->getActiveResumeProfile();
        if ($profile && $profile->hobbies->isNotEmpty()) {
            return $profile->hobbies->pluck('name')->all();
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Job Insights
    |--------------------------------------------------------------------------
    */
    public function extractJobInsightsData(): array
    {
        $text = strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''));

        $urgency = Str::contains($text, ['urgent', 'asap', 'immediately'])
            ? 'High'
            : 'Normal';

        $risk = Str::contains($text, ['bug', 'fix', 'error'])
            ? 'Existing system instability'
            : 'Potential implementation mismatch';

        $goal = Str::contains($text, ['mvp', 'build'])
            ? 'Build working solution quickly'
            : 'Improve or fix existing system';

        return [
            'urgency' => $urgency,
            'risk' => $risk,
            'goal' => $goal,
        ];
    }

    protected function extractJobInsights(): string
    {
        $data = $this->extractJobInsightsData();
        return "- Urgency: {$data['urgency']}\n- Risk: {$data['risk']}\n- Goal: {$data['goal']}";
    }

    /*
    |--------------------------------------------------------------------------
    | Job Helper
    |--------------------------------------------------------------------------
    */
    protected function getJobDetails(): array
    {
        return [
            'title' => $this->job?->title ?? 'N/A',
            'description' => $this->job?->description ?? 'N/A',
        ];
    }
}
