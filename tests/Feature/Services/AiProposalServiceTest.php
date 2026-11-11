<?php

use App\Jobs\GenerateAiJobProposal;
use App\Models\AiJobProposal;
use App\Services\AiProposalService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config([
        'services.ai.enabled_providers' => ['gemini', 'openai'],
        'services.ai.gemini' => [
            'key' => 'gemini-key',
            'model' => 'gemini-test',
            'conversation_id' => 'gemini',
        ],
        'services.ai.openai' => [
            'key' => 'openai-key',
            'model' => 'gpt-test',
            'conversation_id' => 'openai',
        ],
    ]);

    Queue::fake();
});

test('it creates one queued proposal per enabled provider', function () {
    $job = upworkTestJob(4001);

    $count = app(AiProposalService::class)->dispatchForJob($job);

    expect($count)->toBe(2)
        ->and(AiJobProposal::where('job_id', $job->id)->count())->toBe(2)
        ->and(AiJobProposal::where('job_id', $job->id)->pluck('status')->unique()->all())
        ->toBe(['queued']);

    Queue::assertPushedOn('ai', GenerateAiJobProposal::class);
    Queue::assertPushed(GenerateAiJobProposal::class, 2);
});

test('dispatching the same job twice is idempotent', function () {
    $job = upworkTestJob(4002);
    $service = app(AiProposalService::class);

    $service->dispatchForJob($job);

    Queue::fake();

    $count = $service->dispatchForJob($job);

    expect($count)->toBe(0)
        ->and(AiJobProposal::where('job_id', $job->id)->count())->toBe(2);

    Queue::assertNotPushed(GenerateAiJobProposal::class);
});

test('a failed proposal is reset and dispatched again', function () {
    $job = upworkTestJob(4003);

    $proposal = AiJobProposal::create([
        'job_id' => $job->id,
        'provider' => 'gemini',
        'status' => 'failed',
        'model' => 'gemini-test',
        'conversation_id' => 'gemini',
        'proposal' => 'N/A',
        'attempts' => 3,
        'last_error' => 'boom',
    ]);

    $dispatched = app(AiProposalService::class)->dispatchForJob($job, 'gemini');

    $proposal->refresh();

    expect($dispatched)->toBe(1)
        ->and($proposal->status)->toBe('queued')
        ->and($proposal->attempts)->toBe(0)
        ->and($proposal->last_error)->toBeNull();

    Queue::assertPushedOn('ai', GenerateAiJobProposal::class);
});

test('a completed proposal is never regenerated', function () {
    $job = upworkTestJob(4004);

    AiJobProposal::create([
        'job_id' => $job->id,
        'provider' => 'gemini',
        'status' => 'completed',
        'model' => 'gemini-test',
        'conversation_id' => 'gemini',
        'proposal' => 'Done',
    ]);

    $dispatched = app(AiProposalService::class)->dispatchForJob($job, 'gemini');

    expect($dispatched)->toBe(0);

    Queue::assertNotPushed(GenerateAiJobProposal::class);
});
