<?php

namespace App\Jobs;

use App\Ai\Agents\AiJobProposalAgent;
use App\Enums\AiProposalStatusEnum;
use App\Models\AiJobProposal;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

class GenerateAiJobProposal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'ai';

    /**
     * Framework retries for real API exceptions.
     */
    public $tries = 3;

    public $backoff = [15, 60, 180];

    public $timeout = 150;

    public $failOnTimeout = true;

    /**
     * Current re-queue attempt (independent of Laravel's $tries).
     * Starts at 1, increments each time reQueueJob() is called.
     */
    public int $aiCurrentAttempt = 1;

    /**
     * Stop re-queueing after this many rate-limit attempts.
     */
    protected const MAX_REQUEUES = 20;

    /**
     * Max delay cap for exponential backoff (seconds).
     */
    protected const MAX_BACKOFF = 600;

    /**
     * Atomic sliding-window rate limiter.
     * KEYS[1] = key, ARGV[1] = now (seconds), ARGV[2] = window (seconds),
     * ARGV[3] = limit, ARGV[4] = unique member
     */
    protected const RATE_LIMIT_SCRIPT = <<<'LUA'
local key = KEYS[1]
local now = tonumber(ARGV[1])
local window = tonumber(ARGV[2])
local limit = tonumber(ARGV[3])
local member = ARGV[4]

redis.call('ZREMRANGEBYSCORE', key, 0, now - window)

if redis.call('ZCARD', key) >= limit then
    return 0
end

redis.call('ZADD', key, now, member)
redis.call('PEXPIRE', key, window * 1000)

return 1
LUA;

    public function __construct(
        public int $aiJobProposalId,
        int $aiCurrentAttempt = 1,
    ) {
        $this->aiCurrentAttempt = $aiCurrentAttempt;
    }

    public function handle(): void
    {
        $proposal = AiJobProposal::find($this->aiJobProposalId);

        if (!$proposal || $proposal->status === AiProposalStatusEnum::COMPLETED->value) {
            return;
        }

        $provider = $proposal->provider;
        $apiKey = config("services.ai.{$provider}.key", 'unknown') ?? 'unknown';

        $rateLimitKey = "ai_rate:{$provider}:" . md5($apiKey);
        $rateLimit = max(1, (int) config('services.ai.rate_limit', 5));
        $interval = max(1, (int) config('services.ai.rate_limit_interval', 60));

        if (!$this->acquireRateLimit($rateLimitKey, $rateLimit, $interval)) {
            $this->reQueueJob('rate_limit_exceeded');

            return;
        }

        $this->processProposal($proposal);
    }

    protected function acquireRateLimit(string $key, int $limit, int $interval): bool
    {
        $result = Redis::connection()->eval(
            self::RATE_LIMIT_SCRIPT,
            1,
            $key,
            (string) microtime(true),
            (string) $interval,
            (string) $limit,
            (string) Str::uuid()
        );

        return (int) $result === 1;
    }

    protected function processProposal(AiJobProposal $proposal): void
    {
        $job = Job::find($proposal->job_id);

        if (!$job) {
            $this->markFailed($proposal, 'Job not found.');

            return;
        }

        if (empty($proposal->prompt)) {
            $proposal->forceFill([
                'prompt' => $proposal->getPromptText(),
                'instructions' => $proposal->getModelInstructions(),
            ])->save();
        }

        $proposal->forceFill([
            'status' => AiProposalStatusEnum::GENERATING->value,
            'attempts' => $proposal->attempts + 1,
            'last_error' => null,
        ])->save();

        try {
            $agent = app(AiJobProposalAgent::class);
            $agent->setConversationId($proposal->conversation_id);
            $agent->setInstructions($proposal->instructions);

            $response = $agent->prompt(
                prompt: $proposal->prompt,
                provider: $proposal->provider,
                model: $proposal->model
            );

            $proposal->forceFill([
                'proposal' => (string) $response,
                'status' => AiProposalStatusEnum::COMPLETED->value,
                'generated_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            $proposal->forceFill([
                'status' => AiProposalStatusEnum::FAILED->value,
                'last_error' => mb_substr($e->getMessage(), 0, 1000),
                'proposal' => 'Error generating proposal: ' . $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    /**
     * Re-dispatch with exponential backoff for rate-limit reasons.
     */
    protected function reQueueJob(string $reason): void
    {
        $proposal = AiJobProposal::find($this->aiJobProposalId);

        if ($this->aiCurrentAttempt >= self::MAX_REQUEUES) {
            $this->markFailed($proposal, $reason . ' (max attempts reached)');

            Log::error('GenerateAiJobProposal exhausted its retry budget', [
                'reason' => $reason,
                'ai_job_proposal_id' => $this->aiJobProposalId,
                'attempt' => $this->aiCurrentAttempt,
            ]);

            return;
        }

        $delay = $this->getBackoff();

        Log::info('GenerateAiJobProposal requeued', [
            'reason' => $reason,
            'provider' => $proposal?->provider,
            'ai_job_proposal_id' => $this->aiJobProposalId,
            'attempt' => $this->aiCurrentAttempt,
            'delay' => $delay,
        ]);

        static::dispatch($this->aiJobProposalId, $this->aiCurrentAttempt + 1)
            ->onQueue('ai')
            ->delay(now()->addSeconds($delay));

        $this->delete();
    }

    /**
     * Exponential backoff: 10 → 20 → 40 → 80 → 160 → 320 → 600 (cap)
     */
    protected function getBackoff(): int
    {
        return min(10 * (2 ** ($this->aiCurrentAttempt - 1)), self::MAX_BACKOFF);
    }

    protected function markFailed(?AiJobProposal $proposal, string $reason): void
    {
        if (!$proposal) {
            return;
        }

        $proposal->forceFill([
            'status' => AiProposalStatusEnum::FAILED->value,
            'last_error' => mb_substr($reason, 0, 1000),
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        $proposal = AiJobProposal::find($this->aiJobProposalId);

        if ($proposal) {
            $proposal->forceFill([
                'status' => AiProposalStatusEnum::FAILED->value,
                'last_error' => mb_substr($exception->getMessage(), 0, 1000),
            ])->save();
        }

        Log::error('GenerateAiJobProposal failed', [
            'ai_job_proposal_id' => $this->aiJobProposalId,
            'attempt' => $this->aiCurrentAttempt,
            'error' => $exception->getMessage(),
        ]);
    }
}
