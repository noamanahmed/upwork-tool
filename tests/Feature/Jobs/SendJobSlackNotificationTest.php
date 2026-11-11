<?php

use App\Jobs\SendJobSlackNotification;
use App\Models\Job;
use App\Models\JobSearchPivot;
use App\Services\ThirdParty\SlackService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

function createPendingSlackPivot(): array
{
    $job = upworkTestJob(3001);
    $search = upworkTestJobSearch();

    DB::table('job_searches_jobs_pivot')->insert([
        'job_search_id' => $search->id,
        'job_id' => $job->id,
        'is_slack_webhook_sent' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$job, $search];
}

test('a successful send marks the pivot and the job as notified', function () {
    Http::fake(['hooks.slack.test/*' => Http::response('ok', 200)]);

    [$job, $search] = createPendingSlackPivot();

    (new SendJobSlackNotification($job->id, $search->id))->handle(app(SlackService::class));

    $pivot = JobSearchPivot::where('job_id', $job->id)
        ->where('job_search_id', $search->id)
        ->first();

    expect($pivot->is_slack_webhook_sent)->toBeTrue()
        ->and($pivot->slack_sent_at)->not->toBeNull()
        ->and(Job::find($job->id)->is_slack_webhook_sent)->toBeTrue();

    Http::assertSentCount(1);
});

test('an already sent pivot is not sent twice', function () {
    Http::fake(['hooks.slack.test/*' => Http::response('ok', 200)]);

    [$job, $search] = createPendingSlackPivot();

    (new SendJobSlackNotification($job->id, $search->id))->handle(app(SlackService::class));
    (new SendJobSlackNotification($job->id, $search->id))->handle(app(SlackService::class));

    Http::assertSentCount(1);
});

test('a rejected webhook leaves the pivot pending and throws', function () {
    Http::fake(['hooks.slack.test/*' => Http::response('nope', 500)]);

    [$job, $search] = createPendingSlackPivot();

    expect(fn () => (new SendJobSlackNotification($job->id, $search->id))->handle(app(SlackService::class)))
        ->toThrow(RequestException::class);

    $pivot = JobSearchPivot::where('job_id', $job->id)
        ->where('job_search_id', $search->id)
        ->first();

    expect($pivot->is_slack_webhook_sent)->toBeFalse()
        ->and($pivot->slack_sent_at)->toBeNull();
});

test('a search without a webhook is skipped instead of retried', function () {
    Http::fake();

    $job = upworkTestJob(3002);
    $search = upworkTestJobSearch(null, '');

    DB::table('job_searches_jobs_pivot')->insert([
        'job_search_id' => $search->id,
        'job_id' => $job->id,
        'is_slack_webhook_sent' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    (new SendJobSlackNotification($job->id, $search->id))->handle(app(SlackService::class));

    $pivot = JobSearchPivot::where('job_id', $job->id)
        ->where('job_search_id', $search->id)
        ->first();

    expect($pivot->is_slack_webhook_sent)->toBeFalse()
        ->and($pivot->slack_last_error)->toBe('no_webhook');

    Http::assertNothingSent();
});
