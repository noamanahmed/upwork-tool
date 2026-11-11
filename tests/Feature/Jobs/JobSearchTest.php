<?php

use App\Jobs\GenerateAiJobProposal;
use App\Jobs\JobSearch as JobSearchJob;
use App\Jobs\SendJobSlackNotification;
use App\Models\AiJobProposal;
use App\Models\Job;
use App\Services\UpWorkService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config([
        'services.ai.enabled_providers' => ['gemini'],
        'services.ai.gemini' => [
            'key' => 'test-key',
            'model' => 'gemini-test',
            'conversation_id' => 'gemini',
        ],
    ]);

    Queue::fake();
});

test('a new job fans out one ai job and one slack job', function () {
    $search = upworkTestJobSearch();
    $edges = [upworkTestEdge(2001)];

    $this->mock(UpWorkService::class, function ($mock) use ($edges) {
        $mock->shouldReceive('jobs')->once()->andReturn($edges);
    });

    (new JobSearchJob($search->id))->handle();

    $job = Job::where('upwork_id', 2001)->firstOrFail();

    Queue::assertPushedOn('ai', GenerateAiJobProposal::class);
    Queue::assertPushedOn('notifications', SendJobSlackNotification::class);
    Queue::assertPushed(SendJobSlackNotification::class, 1);

    expect(AiJobProposal::where('job_id', $job->id)->where('provider', 'gemini')->value('status'))
        ->toBe('queued');
});

test('running the same search twice does not duplicate work', function () {
    $search = upworkTestJobSearch();
    $edges = [upworkTestEdge(2002)];

    $this->mock(UpWorkService::class, function ($mock) use ($edges) {
        $mock->shouldReceive('jobs')->twice()->andReturn($edges);
    });

    (new JobSearchJob($search->id))->handle();

    Queue::fake();

    (new JobSearchJob($search->id))->handle();

    Queue::assertNotPushed(GenerateAiJobProposal::class);
    Queue::assertNotPushed(SendJobSlackNotification::class);

    expect(Job::count())->toBe(1);
});
