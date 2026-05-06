<?php

namespace App\Models;

use Illuminate\Support\Str;

class AiJobProposal extends BaseModel
{
    protected $casts = [
        'generated_at' => 'datetime',
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
    public function getPromptText(): string
    {
        $job = $this->getJobDetails();
        $context = $this->buildContext();
        $insights = $this->extractJobInsights();

        return <<<EOT
Write a highly tailored Upwork proposal for the following job.

=====================
JOB DETAILS
=====================

Title:
{$job['title']}

Description:
{$job['description']}

=====================
JOB INSIGHTS
=====================
{$insights}

=====================
FREELANCER CONTEXT
=====================

Name: {$context['name']}

Relevant Skills:
{$context['skills']}

Key Achievements:
{$context['achievements']}

{$context['case_study']}

=====================
IMPORTANT CONTEXT USAGE RULES
=====================
- Use at most 2 skills, only if directly relevant
- Use EXACTLY 1 achievement as proof (turn it into a result)
- Use at most 1 case study, only if it closely matches the problem
- Do NOT mention everything
- Prioritize relevance over completeness

=====================
INSTRUCTIONS
=====================
- Keep it concise and sharp
- Focus on solving the client’s problem
- Highlight risk or consequence if done wrong

EOT;
    }

    public function getModelInstructions(): string
    {
        return <<<EOT
You are a senior developer writing a HIGH-CONVERSION Upwork proposal.

Your goal is NOT to sound impressive.
Your goal is to make the client feel: "This person understands my exact problem."

STEP 1: UNDERSTAND THE JOB

Extract silently:
- Real problem (not what client says, what they mean)
- What is likely broken or missing
- What matters most (speed, reliability, UX, cost)

STEP 2: WRITE THE PROPOSAL

1. HOOK (max 25 words)
- Call out exact problem or risk
- No greeting

2. UNDERSTANDING (2–3 lines)
- Rephrase problem simply

3. APPROACH (3–5 lines)
- Practical steps only
- No buzzwords

4. PROOF (1–2 lines)
- Use ONE strong result

5. EDGE
- Either ask 1 smart question OR highlight 1 hidden risk

6. CTA (1 line)

HARD CONSTRAINTS:
- No generic phrases
- No long sentences
- No lists
- Max 220 words

REJECTION RULE:
If this proposal can be reused for another job, DO NOT generate it.
Rewrite until it feels specific to this job.

EOT;
    }

    /*
    |--------------------------------------------------------------------------
    | Core Context Builder
    |--------------------------------------------------------------------------
    */
    protected function buildContext(): array
    {
        $config = config('admin');
        $jobText = $this->normalizeKeywords(
            strtolower($this->job->title . ' ' . $this->job->description)
        );

        $skills = $this->selectRelevantSkills($config['skills'], $jobText);
        $caseStudies = $this->selectCaseStudies($config, $jobText);
        $achievements = $this->selectAchievements($config, $jobText);

        return [
            'name' => $config['personal']['first_name'] . ' ' . $config['personal']['last_name'],
            'skills' => $this->formatSkills($skills),
            'achievements' => $this->formatAchievements($achievements),
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

    protected function selectCaseStudies(array $config, string $jobText): array
    {
        $caseStudies = $config['case_studies'];
        $limit = $config['ai_rules']['max_case_studies'] ?? 1;

        $matched = [];

        foreach ($caseStudies as $caseStudy) {
            foreach ($caseStudy['tags'] as $tag) {
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

        foreach ($config['achievements'] as $group => $items) {
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
    | Job Insights (NEW)
    |--------------------------------------------------------------------------
    */
    protected function extractJobInsights(): string
    {
        $text = strtolower($this->job->title . ' ' . $this->job->description);

        $urgency = Str::contains($text, ['urgent', 'asap', 'immediately'])
            ? 'High'
            : 'Normal';

        $risk = Str::contains($text, ['bug', 'fix', 'error'])
            ? 'Existing system instability'
            : 'Potential implementation mismatch';

        $goal = Str::contains($text, ['mvp', 'build'])
            ? 'Build working solution quickly'
            : 'Improve or fix existing system';

        return "- Urgency: {$urgency}\n- Risk: {$risk}\n- Goal: {$goal}";
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
            'title' => $this->job->title ?? 'N/A',
            'description' => $this->job->description ?? 'N/A',
        ];
    }
}