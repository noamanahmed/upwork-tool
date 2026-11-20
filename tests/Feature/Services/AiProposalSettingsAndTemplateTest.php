<?php

use App\Models\AiJobProposal;
use App\Models\Job;
use App\Models\Setting;
use App\Services\AiProposalService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Setting::query()->delete();
    (new \Database\Seeders\SettingSeeder())->run();
    (new \Database\Seeders\ResumeSeeder())->run();
});

test('it renders default prompt template with job and context variables', function () {
    $job = upworkTestJob(5001);
    $proposal = new AiJobProposal([
        'job_id' => $job->id,
        'provider' => 'gemini',
    ]);
    $proposal->setRelation('job', $job);

    $prompt = $proposal->getPromptText();
    $instructions = $proposal->getModelInstructions();

    expect($prompt)
        ->toContain('Laravel job 5001')
        ->toContain('Build something')
        ->toContain('JOB DETAILS')
        ->toContain('JOB INSIGHTS');

    expect($instructions)
        ->toContain('You are a senior developer writing HIGH-CONVERSION Upwork proposals')
        ->toContain('STEP 1 — UNDERSTAND THE JOB');
});

test('it allows customizing the global prompt template via settings', function () {
    $job = upworkTestJob(5002);
    $proposal = new AiJobProposal([
        'job_id' => $job->id,
        'provider' => 'gemini',
    ]);
    $proposal->setRelation('job', $job);

    Setting::set('AI_PROMPT_TEMPLATE', 'Proposal for {{ job.title }}. Budget: {{ job.budget }}. Freelancer: {{ freelancer.name }}');
    Setting::set('AI_SYSTEM_PROMPT', 'System prompt for {{ job.title }}');

    expect($proposal->getPromptText())
        ->toBe("Proposal for Laravel job 5002. Budget: . Freelancer: Nauman Ahmed");

    expect($proposal->getModelInstructions())
        ->toBe("System prompt for Laravel job 5002");
});

test('it supports provider-specific system prompts and prompt templates', function () {
    $job = upworkTestJob(5003);

    $geminiProposal = new AiJobProposal(['job_id' => $job->id, 'provider' => 'gemini']);
    $geminiProposal->setRelation('job', $job);

    $openaiProposal = new AiJobProposal(['job_id' => $job->id, 'provider' => 'openai']);
    $openaiProposal->setRelation('job', $job);

    // Global prompts
    Setting::set('AI_PROMPT_TEMPLATE', 'Global prompt for {{ job.title }}');
    Setting::set('AI_SYSTEM_PROMPT', 'Global system instructions');

    // OpenAI overrides
    Setting::set('AI_PROMPT_TEMPLATE_OPENAI', 'OpenAI custom prompt for {{ job.title }}');
    Setting::set('AI_SYSTEM_PROMPT_OPENAI', 'OpenAI custom instructions for {{ job.title }}');

    // Gemini should inherit global
    expect($geminiProposal->getPromptText())->toBe('Global prompt for Laravel job 5003');
    expect($geminiProposal->getModelInstructions())->toBe('Global system instructions');

    // OpenAI should use its specific overrides
    expect($openaiProposal->getPromptText())->toBe('OpenAI custom prompt for Laravel job 5003');
    expect($openaiProposal->getModelInstructions())->toBe('OpenAI custom instructions for Laravel job 5003');
});

test('it enables and disables providers dynamically and updates config', function () {
    Setting::set('AI_PROVIDER', 'gemini');
    Setting::set('AI_GEMINI_ENABLED', '1');
    Setting::set('AI_OPENAI_ENABLED', '1');
    Setting::set('AI_ANTHROPIC_ENABLED', '0');

    Setting::set('OPENAI_API_KEY', 'sk-test-secret', null, true);
    Setting::set('OPENAI_MODEL', 'gpt-4o');

    Setting::applyToConfig();

    $enabled = config('services.ai.enabled_providers');
    expect($enabled)->toContain('gemini', 'openai');
    expect($enabled)->not->toContain('anthropic');

    expect(config('services.ai.openai.key'))->toBe('sk-test-secret');
    expect(config('ai.providers.openai.key'))->toBe('sk-test-secret');
    expect(config('services.ai.openai.model'))->toBe('gpt-4o');

    // Now disable OpenAI
    Setting::set('AI_OPENAI_ENABLED', '0');
    Setting::applyToConfig();

    expect(config('services.ai.enabled_providers'))->not->toContain('openai');
});

test('it dispatches proposals with dynamic template content', function () {
    $job = upworkTestJob(5004);

    config([
        'services.ai.enabled_providers' => ['gemini'],
        'services.ai.gemini' => [
            'key' => 'gemini-test-key',
            'model' => 'gemini-flash-latest',
            'conversation_id' => 'gemini',
        ],
    ]);

    Setting::set('AI_PROMPT_TEMPLATE_GEMINI', 'Tailored for {{ job.title }}');
    Setting::set('AI_SYSTEM_PROMPT_GEMINI', 'Act as specialized Gemini agent for {{ job.title }}');

    $dispatched = app(AiProposalService::class)->dispatchForJob($job, 'gemini');

    expect($dispatched)->toBe(1);

    $proposal = AiJobProposal::where('job_id', $job->id)->where('provider', 'gemini')->first();
    expect($proposal)->not->toBeNull();
    expect($proposal->prompt)->toBe('Tailored for Laravel job 5004');
    expect($proposal->instructions)->toBe('Act as specialized Gemini agent for Laravel job 5004');
});

test('it does not fallback to hardcoded config or static methods when database records are missing', function () {
    Setting::query()->delete();
    \App\Models\ResumeProfile::query()->delete();

    $job = upworkTestJob(5005);
    $proposal = new AiJobProposal([
        'job_id' => $job->id,
        'provider' => 'gemini',
    ]);
    $proposal->setRelation('job', $job);

    expect($proposal->getPromptText())->toBe('');
    expect($proposal->getModelInstructions())->toBe('');

    $context = $proposal->buildTemplateContext();
    expect($context['freelancer']['name'])->toBe('');
    expect($context['freelancer']['skills'])->toBe('');
    expect($context['freelancer']['experience'])->toBe('');
    expect($context['freelancer']['case_study'])->toBe('');
});
