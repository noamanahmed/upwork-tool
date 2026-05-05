<?php

namespace App\Jobs;

use App\Models\AiJobProposal;
use App\Models\Job;
use App\Ai\Agents\AiJobProposalAgent;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class GenerateAiJobProposal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry attempts
     */
    public $tries = 3;

    /**
     * Retry delay (seconds)
     */
    public $backoff = 15;

    public function __construct(
        public AiJobProposal $aiJobProposal
    ) {}

    public function handle(): void
    {
        $provider = $this->aiJobProposal->provider;
        $apiKey = config("services.ai.{$provider}.key", 'unknown');

        // Redis Keys (isolated per provider + key)
        $rateLimitKey = "ai_rate:{$provider}:" . md5($apiKey);
        $semaphoreKey = "ai_parallel:{$provider}";

        // Config
        $rateLimit = (int) config('services.ai.rate_limit', 5);              // e.g. 5 requests
        $interval  = (int) config('services.ai.rate_limit_interval', 60);    // per 60 seconds
        $maxParallel = (int) config('services.ai.max_parallel_requests', 3); // concurrent requests

        // STEP 1: Acquire parallel slot
        if (!$this->acquireSemaphore($semaphoreKey, $maxParallel)) {
            $this->reQueueJob('parallel_limit_exceeded');
            return;
        }

        try {
            // STEP 2: Apply rate limiting
            if (!$this->acquireRateLimit($rateLimitKey, $rateLimit, $interval)) {
                $this->reQueueJob('rate_limit_exceeded');
                return;
            }

            // STEP 3: Process job
            $this->processProposal();

        } finally {
            // STEP 4: Always release semaphore
            $this->releaseSemaphore($semaphoreKey);
        }
    }

    /**
     * Sliding window rate limiter using Redis ZSET
     */
    protected function acquireRateLimit(string $key, int $limit, int $interval): bool
    {
        $redis = Redis::connection();
        $now = microtime(true);

        // Remove expired entries
        $redis->zremrangebyscore($key, 0, $now - $interval);

        // Count current requests
        $count = $redis->zcard($key);

        if ($count >= $limit) {
            return false;
        }

        // Add current request
        $redis->zadd($key, [$now => $now]);

        // Ensure TTL
        $redis->expire($key, $interval);

        return true;
    }

    /**
     * Acquire semaphore (limit concurrent requests)
     */
    protected function acquireSemaphore(string $key, int $max): bool
    {
        $redis = Redis::connection();

        $current = $redis->incr($key);

        if ($current > $max) {
            $redis->decr($key);
            return false;
        }

        // Prevent deadlocks (worker crash safety)
        $redis->expire($key, 60);

        return true;
    }

    /**
     * Release semaphore safely
     */
    protected function releaseSemaphore(string $key): void
    {
        $redis = Redis::connection();

        try {
            if ($redis->get($key) > 0) {
                $redis->decr($key);
            }
        } catch (Exception $e) {
            Log::warning('Semaphore release failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Main AI processing logic
     */
    protected function processProposal(): void
    {
        try {
            $job = Job::find($this->aiJobProposal->job_id);

            if (!$job) {
                throw new Exception("Job not found.");
            }

            $agent = app(AiJobProposalAgent::class);
            $agent->setConversationId($this->aiJobProposal->conversation_id);
            $agent->setInstructions($this->aiJobProposal->instructions);

            $response = $agent->prompt(
                prompt: $this->aiJobProposal->prompt,
                provider: $this->aiJobProposal->provider,
                model: $this->aiJobProposal->model
            );

            $this->aiJobProposal->update([
                'proposal'     => (string) $response,
                'status'       => 'completed',
                'generated_at' => now(),
            ]);

        } catch (Exception $e) {
            $this->aiJobProposal->update([
                'status'   => 'failed',
                'proposal' => 'Error generating proposal: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Re-dispatch job with delay
     */
    protected function reQueueJob(string $reason = 'unknown', array $context = []): void
    {
        Log::info('GenerateAiJobProposal requeued', array_merge([
            'reason' => $reason,
            'provider' => $this->aiJobProposal->provider,
            'ai_job_proposal_id' => $this->aiJobProposal->id,
        ], $context));

        static::dispatch($this->aiJobProposal)
            ->delay(now()->addSeconds(10));

        $this->delete();
    }
}