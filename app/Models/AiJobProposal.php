<?php

namespace App\Models;

use Illuminate\Support\Str;

class AiJobProposal extends BaseModel
{
    protected $fillable = [
        'name',
        'job_id',
        'proposal',
        'status',
        'provider',
        'model',
        'conversation_id',
        'description',
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

        return <<<EOT
Write a highly tailored Upwork proposal for the following job.

IMPORTANT:
- Use ONLY relevant information from provided context
- Do not dump all skills or experience
- Focus on solving the client’s problem

=====================
JOB DETAILS
=====================

Title:
{$job['title']}

Description:
{$job['description']}

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
INSTRUCTIONS
=====================
- Pick only what is relevant
- Keep it concise and sharp
- Focus on results and confidence

EOT;
    }

    public function getModelInstructions(): string
    {
        return <<<EOT
You are an expert Upwork proposal writer focused on HIGH-CONVERSION technical proposals.

CRITICAL RULES:

1. FIRST LINE = HOOK
- First line must grab attention (max 30–40 words)
- No greetings
- Directly address problem or show confidence

2. HUMAN STYLE
- Write like a real developer
- Simple, natural English
- No fluff, no buzzwords

3. PRECISION
- Use ONLY relevant context provided
- Do NOT list all skills
- Do NOT repeat job description

4. STRUCTURE
- Hook
- Show understanding
- Explain approach (technical but brief)
- Add 1 strong proof (achievement or case study)
- End with simple CTA

5. LENGTH
- 150–250 words
- Short paragraphs

6. EDGE
- Add 1 technical insight OR ask 1 smart question

GOAL:
Client should feel:
"This developer understands my problem and knows how to solve it."

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
        $jobText = strtolower($this->job->title . ' ' . $this->job->description);

        $skills = $this->selectRelevantSkills($config['skills'], $jobText);
        $caseStudy = $this->selectCaseStudy($config['case_studies'], $jobText);
        $achievements = $this->selectAchievements($config);

        return [
            'name' => $config['personal']['first_name'] . ' ' . $config['personal']['last_name'],
            'skills' => $this->formatSkills($skills),
            'achievements' => $this->formatAchievements($achievements),
            'case_study' => $this->formatCaseStudy($caseStudy),
        ];
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

    protected function selectCaseStudy(array $caseStudies, string $jobText): ?array
    {
        foreach ($caseStudies as $caseStudy) {
            foreach ($caseStudy['tags'] as $tag) {
                if (Str::contains($jobText, Str::lower($tag))) {
                    return $caseStudy;
                }
            }
        }

        return null;
    }

    protected function selectAchievements(array $config): array
    {
        $limit = $config['ai_rules']['max_achievements'] ?? 2;
        return array_slice($config['achievements'], 0, $limit);
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

        $output = '';

        foreach ($skills as $group => $items) {
            $output .= ucfirst($group) . ': ' . implode(', ', array_unique($items)) . "\n";
        }

        return trim($output);
    }

    protected function formatAchievements(array $achievements): string
    {
        return '- ' . implode("\n- ", $achievements);
    }

    protected function formatCaseStudy(?array $caseStudy): string
    {
        if (!$caseStudy) {
            return '';
        }

        return <<<EOT
Relevant Past Work:
- {$caseStudy['title']}
- Problem: {$caseStudy['problem']}
- Solution: {$caseStudy['solution']}
- Result: {$caseStudy['result']}
EOT;
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