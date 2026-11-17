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
            $template = static::defaultPromptTemplate();
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
            $template = static::defaultSystemPrompt();
        }

        $context = $this->buildTemplateContext($provider);

        return MustacheEngine::render($template, $context);
    }

    /**
     * Default proposal prompt template using Mustache syntax tags.
     */
    public static function defaultPromptTemplate(): string
    {
        return <<<'EOT'
Write a highly tailored Upwork proposal for the following job.

The proposal must feel:
- highly specific
- practical
- trustworthy
- written after carefully reading the entire job post

The proposal must NOT feel:
- generic
- reusable
- AI-generated
- like a resume dump

=====================
JOB DETAILS
=====================

Title:
{{ job.title }}

Description:
{{ job.description }}

{{#job.budget}}
Budget:
{{ job.budget }}
{{/job.budget}}

=====================
JOB INSIGHTS
=====================

{{ insights }}


=====================
FREELANCER CONTEXT
=====================

Name:
{{ freelancer.name }}

Relevant Skills:
{{ freelancer.skills }}

Key Achievement:
{{ freelancer.achievements }}

Relevant Experience:
{{ freelancer.experience }}

Relevant Certification:
{{ freelancer.certifications }}

{{ freelancer.case_study }}

=====================
CONTEXT PRIORITY RULES
=====================

The goal is NOT to mention the most information.

The goal is to include only information that increases trust for THIS exact project.

--------------------------------------------------
CLIENT REQUIREMENTS HAVE HIGHEST PRIORITY
--------------------------------------------------

If the client explicitly asks for:
- examples
- timeline
- pricing
- process
- experience
- deliverables
- availability
- requirements to begin

You MUST answer those directly.

Missing requested information is considered proposal failure.

--------------------------------------------------
SKILLS
--------------------------------------------------

- Mention only skills directly related to the actual work
- Prefer technologies explicitly mentioned in the job description
- Default to 2 highly relevant skills
- You may include more ONLY if:
  - the project clearly spans multiple responsibilities
  - multiple technologies are central to execution
  - additional skills increase implementation confidence

Avoid unnecessary tech-stack dumping.

Bad:
Laravel, Vue.js, AWS, Docker, Redis, Kubernetes

Good:
Elementor, WooCommerce, Porto theme customization

--------------------------------------------------
ACHIEVEMENTS
--------------------------------------------------

- Use EXACTLY ONE achievement
- The achievement must support the client’s primary concern
- Convert achievements into practical proof/results
- Prefer measurable outcomes

Good:
Managed and maintained 2000+ WordPress websites.

Bad:
I am highly experienced and passionate.

--------------------------------------------------
CASE STUDIES
--------------------------------------------------

- Use at most ONE case study
- Only include it if it closely matches:
  - the business type
  - the technical stack
  - the implementation challenge

Do NOT force unrelated case studies.

The case study should feel like:
"I solved a very similar problem before."

--------------------------------------------------
EXPERIENCE
--------------------------------------------------

- Mention only highly relevant experience
- Avoid resume-style summaries
- Focus on execution confidence, not career history

--------------------------------------------------
CERTIFICATIONS
--------------------------------------------------

- Mention certifications only if they meaningfully increase trust
- Ignore certifications for simple projects where execution matters more

--------------------------------------------------
RELEVANCE FILTER
--------------------------------------------------

Before mentioning any skill, achievement, experience, or certification, ask:

"Does this increase confidence for THIS exact project?"

If not, do NOT include it.

--------------------------------------------------
ANTI-GENERIC RULE
--------------------------------------------------

Every sentence must feel connected to:
- this client
- this project
- this stack
- this business goal
- this implementation challenge

If the proposal can be reused for another job, rewrite it.

=====================
OUTPUT REQUIREMENTS
=====================

- First analzye the job and context silently, and understand what client is requiring and what matters most to them.
- Keep it concise and sharp
- Focus on solving the client’s actual problem
- Highlight risks, mistakes, or implementation issues where relevant
- Use natural human language
- No buzzwords
- No fluff
- No markdown
- Plain text only
- Maximum 220 words per proposal
- Short paragraphs preferred
- Do not use bullet spam
- Sound like a senior engineer, not a salesperson

EOT;
    }

    /**
     * Default system instructions template using Mustache syntax tags.
     */
    public static function defaultSystemPrompt(): string
    {
        return <<<'EOT'
You are a senior developer writing HIGH-CONVERSION Upwork proposals.

Your goal is NOT to sound impressive.

Your goal is:
1. Make the client feel understood
2. Follow ALL application instructions exactly
3. Remove any doubt that you actually read the job

==================================================
STEP 1 — UNDERSTAND THE JOB
==================================================

Silently extract:

- The real technical problem
- What is likely broken, risky, or missing
- What matters most:
  speed, UX, reliability, launch quality, communication, or budget

Also identify:

- Explicit application requirements
- Questions the client asked
- Deliverables the client requested in proposal
- Screening instructions
- Required pricing/timeline details

==================================================
STEP 2 — PRIORITIZE REQUIREMENTS
==================================================

If the client explicitly asks for:
- examples
- timeline
- pricing
- experience
- process
- deliverables
- required information

You MUST answer ALL of them.

Missing requested information is considered proposal failure.

The proposal should feel:
- customized
- compliant
- practical
- low-risk

==================================================
STEP 3 — WRITE THE PROPOSAL
==================================================

Structure:

1. Hook
- Max 25 words
- Mention exact problem or launch risk
- No greeting

2. Understanding
- Rephrase their actual goal simply

3. Execution Approach
- Practical implementation steps only
- Mention relevant stack/tools only if useful

4. Proof
- Use ONE highly relevant result or experience

5. Explicit Requirement Responses
- Answer every direct request from the client
- Include:
  timeline
  pricing
  examples
  requirements
  process
  requested experience

6. Edge
- Mention one hidden risk OR ask one smart implementation question

7. CTA
- One short line

==================================================
HARD CONSTRAINTS
==================================================

- No generic phrases
- No buzzwords
- No resume dumping
- No long paragraphs
- No markdown
- Max 220 words
- Every sentence must feel specific to THIS job

==================================================
VALIDATION RULE
==================================================

Before finalizing, silently verify:

- Did I answer every client request? (If not, rewrite)
- Did I include requested pricing/timeline if present? (If not, rewrite)
- Can this proposal be reused for another job? (If yes, rewrite)
- Did I include unnecessary information? (If yes, rewrite)
- Would this pass a manual screening test? (If not, rewrite)

==================================================
NUMBER OF PROPOSALS CONTENT TO GENERATE
==================================================
- Generate different hooks, understandings, execution approaches, proofs, requirement responses, edges, and CTAs to test which ones perform best.
- Generate at least 10 variations of each component for A/B testing.  
- Combine them into at least 20 complete proposals to find the best performing one.

==================================================
OUTPUT FORMAT
==================================================

------------------
--- Hook 1 ---
{Hook Text}
------------------
------------------
------------------
--- Hook 2 ---
{Hook Text}
------------------
------------------
--- Understanding 1 ---
{Understanding Text}
------------------
------------------
--- Understanding 2 ---
{Understanding Text}
------------------
------------------
--- Execution Approach 1 ---
{Execution Approach Text}
------------------
------------------
--- Execution Approach 2 ---
{Execution Approach Text}
------------------
------------------
------------------
--- Proof 1 ---
{Proof Text}
------------------
------------------
--- Proof 2 ---
{Proof Text}
------------------
------------------
------------------
--- Explicit Requirement Responses 1 ---
{Explicit Requirement Responses Text}
------------------
------------------
--- Explicit Requirement Responses 2 ---
{Explicit Requirement Responses Text}
------------------
------------------
--- Edge 1 ---
{Edge Text}
------------------
------------------
--- Edge 2 ---
{Edge Text}
------------------
------------------
------------------
--- CTA 1 ---
{CTA Text}
------------------
------------------
--- CTA 2 ---
{CTA Text}
------------------
------------------

--- Complete Proposal 1 ---
{Complete Proposal Text}
------------------
------------------
--- Complete Proposal 2 ---
{Complete Proposal Text}
------------------
------------------

EOT;
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

        $config = config('admin', []);
        $skillsList = $this->getRawMatchedSkills();
        $achievementsList = $this->getRawMatchedAchievements();
        $caseStudiesList = $this->getRawMatchedCaseStudies();

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
                'name' => $freelancerContext['name'],
                'first_name' => $config['personal']['first_name'] ?? '',
                'last_name' => $config['personal']['last_name'] ?? '',
                'skills' => $freelancerContext['skills'],
                'skills_list' => $skillsList,
                'achievements' => $freelancerContext['achievements'],
                'achievements_list' => $achievementsList,
                'experience' => $freelancerContext['experience'],
                'certifications' => $freelancerContext['certifications'],
                'case_study' => $freelancerContext['case_study'],
                'case_studies' => $caseStudiesList,
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
        $config = config('admin', []);
        $jobText = $this->normalizeKeywords(
            strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
        );

        $skills = $this->selectRelevantSkills($config['skills'] ?? [], $jobText);
        $caseStudies = $this->selectCaseStudies($config, $jobText);
        $achievements = $this->selectAchievements($config, $jobText);

        $firstName = $config['personal']['first_name'] ?? '';
        $lastName = $config['personal']['last_name'] ?? '';

        return [
            'name' => trim($firstName . ' ' . $lastName),
            'skills' => $this->formatSkills($skills),
            'achievements' => $this->formatAchievements($achievements),
            'experience' => '',
            'certifications' => '',
            'case_study' => $this->formatCaseStudies($caseStudies),
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
    protected function selectRelevantSkills(array $skills, string $jobText): array
    {
        $matched = [];

        foreach ($skills as $group => $items) {
            foreach ($items as $category) {
                foreach ((array) $category as $skill) {
                    if (Str::contains($jobText, Str::lower($skill))) {
                        $matched[$group][] = $skill;
                    }
                }
            }
        }

        return $matched;
    }

    public function getRawMatchedSkills(): array
    {
        $config = config('admin', []);
        $skills = $config['skills'] ?? [];
        $jobText = $this->normalizeKeywords(
            strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
        );
        $matched = $this->selectRelevantSkills($skills, $jobText);
        $flat = [];
        foreach ($matched as $items) {
            $flat = array_merge($flat, $items);
        }
        return array_values(array_unique($flat));
    }

    public function getRawMatchedAchievements(): array
    {
        $config = config('admin', []);
        $jobText = $this->normalizeKeywords(
            strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
        );
        return array_values($this->selectAchievements($config, $jobText));
    }

    public function getRawMatchedCaseStudies(): array
    {
        $config = config('admin', []);
        $jobText = $this->normalizeKeywords(
            strtolower(($this->job?->title ?? '') . ' ' . ($this->job?->description ?? ''))
        );
        return array_values($this->selectCaseStudies($config, $jobText));
    }

    protected function selectCaseStudies(array $config, string $jobText): array
    {
        $caseStudies = $config['case_studies'] ?? [];
        $limit = $config['ai_rules']['max_case_studies'] ?? 1;

        $matched = [];

        foreach ($caseStudies as $caseStudy) {
            foreach ($caseStudy['tags'] ?? [] as $tag) {
                if (Str::contains($jobText, Str::lower($tag))) {
                    $matched[] = $caseStudy;
                    break;
                }
            }
        }

        return array_slice($matched, 0, $limit);
    }

    protected function selectAchievements(array $config, string $jobText): array
    {
        $limit = $config['ai_rules']['max_achievements'] ?? 1;
        $matched = [];

        foreach ($config['achievements'] ?? [] as $group => $items) {
            if (Str::contains($jobText, strtolower($group))) {
                $matched = array_merge($matched, $items);
            }
        }

        // Safer fallback (only top general)
        if (empty($matched) && isset($config['achievements']['general'])) {
            $matched[] = $config['achievements']['general'][0];
        }

        return array_slice(array_unique($matched), 0, $limit);
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
    | Formatters
    |--------------------------------------------------------------------------
    */
    protected function formatSkills(array $skills): string
    {
        if (empty($skills)) {
            return 'N/A';
        }

        $flat = [];
        foreach ($skills as $items) {
            $flat = array_merge($flat, $items);
        }

        return implode(', ', array_unique($flat));
    }

    protected function formatAchievements(array $achievements): string
    {
        return implode("\n", $achievements);
    }

    protected function formatCaseStudies(array $caseStudies): string
    {
        if (empty($caseStudies)) {
            return '';
        }

        $case = $caseStudies[0];

        return "Relevant Past Work:\n" .
            "- {$case['title']} → {$case['result']}";
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
