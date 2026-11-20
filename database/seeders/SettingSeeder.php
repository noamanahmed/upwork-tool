<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Default settings dictionary with their keys, default values, encryption requirement, and descriptions.
     */
    public static function defaults(): array
    {
        return [
            // Translation
            'RAPID_API_TRANSLATION_API_KEY' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'RapidAPI Translation Key for automatic translation',
            ],

            // Upwork OAuth
            'UPWORK_CLIENT_ID' => [
                'default_value' => '',
                'is_encrypted' => false,
                'description' => 'Upwork OAuth Client ID',
            ],
            'UPWORK_CLIENT_SECRET' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'Upwork OAuth Client Secret',
            ],
            'UPWORK_REDIRECT_URI' => [
                'default_value' => 'http://127.0.0.1:10999',
                'is_encrypted' => false,
                'description' => 'Upwork OAuth Redirect URI base',
            ],

            // Slack
            'SLACK_WEBHOOK_URL' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'Slack Incoming Webhook URL for job notifications',
            ],

            // Upwork Cron
            'UPWORK_CRON_START' => [
                'default_value' => '21:00',
                'is_encrypted' => false,
                'description' => 'Upwork scheduler start time (Pakistan time)',
            ],
            'UPWORK_CRON_END' => [
                'default_value' => '03:00',
                'is_encrypted' => false,
                'description' => 'Upwork scheduler end time (Pakistan time)',
            ],
            'UPWORK_CRON_DAYS' => [
                'default_value' => 'mon,tue,wed,thu,fri',
                'is_encrypted' => false,
                'description' => 'Active days of the week for Upwork scheduler',
            ],
            'UPWORK_CRON_ENABLED' => [
                'default_value' => '1',
                'is_encrypted' => false,
                'description' => 'Master toggle for Upwork background scheduler',
            ],

            // AI Configuration
            'AI_PROVIDER' => [
                'default_value' => 'gemini',
                'is_encrypted' => false,
                'description' => 'Active default AI provider for proposals',
            ],
            'AI_RATE_LIMIT' => [
                'default_value' => '5',
                'is_encrypted' => false,
                'description' => 'AI requests rate limit',
            ],
            'AI_RATE_LIMIT_INTERVAL' => [
                'default_value' => '60',
                'is_encrypted' => false,
                'description' => 'AI rate limit interval in seconds',
            ],
            'AI_MAX_PARALLEL_REQUESTS' => [
                'default_value' => '10',
                'is_encrypted' => false,
                'description' => 'AI maximum parallel requests',
            ],
            'AI_PARALLEL_REQUEST_TIMEOUT' => [
                'default_value' => '60',
                'is_encrypted' => false,
                'description' => 'AI parallel request timeout in seconds',
            ],

            // Global Proposal Prompts (Mustache)
            'AI_SYSTEM_PROMPT' => [
                'default_value' => static::defaultSystemPrompt(),
                'is_encrypted' => false,
                'description' => 'Default system prompt instructions for AI job proposals (Mustache tags supported)',
            ],
            'AI_PROMPT_TEMPLATE' => [
                'default_value' => static::defaultPromptTemplate(),
                'is_encrypted' => false,
                'description' => 'Default user prompt template for AI job proposals (Mustache tags supported)',
            ],


            // AI Providers (Credentials, Models, Enable/Disable, and Per-Provider Prompts)
            ...static::providerDefaults(),
        ];
    }

    /**
     * Generate default settings for all AI providers.
     */
    protected static function providerDefaults(): array
    {
        $defaults = [];

        foreach (Setting::AI_PROVIDERS as $provider => $meta) {
            $u = strtoupper($provider);

            $defaults["AI_{$u}_ENABLED"] = [
                'default_value' => $meta['default_enabled'] ? '1' : '0',
                'is_encrypted' => false,
                'description' => "Enable/disable {$meta['name']} provider for proposal generation",
            ];

            $defaults["{$u}_API_KEY"] = [
                'default_value' => '',
                'is_encrypted' => $meta['key_encrypted'],
                'description' => "{$meta['name']} API key",
            ];

            $defaults["{$u}_MODEL"] = [
                'default_value' => $meta['default_model'],
                'is_encrypted' => false,
                'description' => "{$meta['name']} model name",
            ];

            $defaults["{$u}_CONVERSATION_ID"] = [
                'default_value' => $provider,
                'is_encrypted' => false,
                'description' => "{$meta['name']} conversation identifier",
            ];

            $defaults["AI_SYSTEM_PROMPT_{$u}"] = [
                'default_value' => '',
                'is_encrypted' => false,
                'description' => "Custom system prompt for {$meta['name']} (leave empty for default)",
            ];

            $defaults["AI_PROMPT_TEMPLATE_{$u}"] = [
                'default_value' => '',
                'is_encrypted' => false,
                'description' => "Custom proposal prompt template for {$meta['name']} (leave empty for default)",
            ];

            if (!empty($meta['extra_fields'])) {
                foreach ($meta['extra_fields'] as $extraKey => $extraMeta) {
                    $defaults[$extraKey] = [
                        'default_value' => $extraMeta['default'],
                        'is_encrypted' => $extraMeta['encrypted'],
                        'description' => "{$meta['name']} {$extraMeta['label']}",
                    ];
                }
            }
        }

        return $defaults;
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
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (static::defaults() as $key => $meta) {
            $setting = Setting::where('key', $key)->orWhere('name', $key)->first();
            if (!$setting) {
                $setting = new Setting();
                $setting->key = $key;
                $setting->name = $key;
            }

            $setting->default_value = $meta['default_value'];
            $setting->is_encrypted = $meta['is_encrypted'];
            $setting->description = $meta['description'];

            // If an env value exists and no custom value is in DB, we can optionally leave value null
            // so it dynamically falls back to .env, or if we want to initialize it:
            $setting->save();
        }
    }
}
