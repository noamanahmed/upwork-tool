<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying a failed job.
     */
    public $backoff = 15;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public \App\Models\AiJobProposal $aiJobProposal
    ) {
    }

    public function handle(): void
    {
        $provider = $this->aiJobProposal->provider;
        $apiKey = config("services.ai.{$provider}.key", 'unknown');

        // Key for rate limiting (per provider + api key)
        $rateLimitKey = 'generate_ai_proposal_' . $provider . '_' . md5($apiKey);
        $rateLimit = (int) config('services.ai.rate_limit', 5);
        $rateLimitInterval = (int) config('services.ai.rate_limit_interval', 60);

        // Key for concurrency/parallel control (global across all providers)
        $semaphoreKey = 'generate_ai_proposal_semaphore';
        $maxParallel = (int) config('services.ai.max_parallel_requests', 0);
        $semaphoreTimeout = (int) config('services.ai.parallel_request_timeout', 60);

        // Step 1: Check concurrency limit if enabled
        if ($maxParallel > 0) {
            $acquired = $this->acquireSemaphore($semaphoreKey, $maxParallel, $semaphoreTimeout);

            if (!$acquired) {
                // Could not acquire semaphore within timeout - too many parallel requests
                $this->reQueueJob('parallel_limit_exceeded');
                return;
            }
        }


        // Step 2: Apply rate limiting (requests per interval)
        Redis::throttle($rateLimitKey)
            ->block(0)->allow($rateLimit)->every($rateLimitInterval)
            ->then(function () {
                $this->processProposal();
            }, function () {
                // Could not obtain lock; push the job back onto the queue
                $this->reQueueJob('rate_limit_exceeded');
            });
    }

    /**
     * Acquire a semaphore lock for concurrency control.
     *
     * @param string $key Redis key for the semaphore
     * @param int $maxConcurrent Max concurrent executions allowed
     * @param int $timeout Seconds to wait before giving up
     * @return bool Whether semaphore was acquired
     */
    protected function acquireSemaphore(string $key, int $maxConcurrent, int $timeout): bool
    {
        $startTime = time();
        $redis = Redis::connection();

        while(true){
            # code...
        
            // Atomically increment and get current count
            $current = $redis->incr($key);

            if ($current <= $maxConcurrent) {
                // Successfully acquired - set expiry as safety net
                if ($current === 1) {
                    // Only set expiry on first acquire
                    $redis->expire($key, $timeout);
                }
                return true;
            }

            // Limit exceeded - rollback our increment
            $redis->decr($key);

            // Check if we've exceeded timeout
            if ((time() - $startTime) >= $timeout) {
                return false;
            }

            // Wait a bit before retrying
            usleep(500000); // 500ms
        }

        return false;
    }

    protected function processProposal(): void
    {
        try {
            $jobId = $this->aiJobProposal->job_id;
            $job = \App\Models\Job::find($jobId);

            if (!$job) {
                throw new \Exception("Job not found.");
            }

            $promptText = $this->aiJobProposal->prompt;
            $agent = app(\App\Ai\Agents\AiJobProposalAgent::class);
            $agent->setConversationId($this->aiJobProposal->conversation_id);
            $agent->setInstructions($this->aiJobProposal->instructions);

            // Execute using the stored provider and model
            $response = $agent->prompt(
                prompt: $promptText,
                provider: $this->aiJobProposal->provider,
                model: $this->aiJobProposal->model
            );

            $this->aiJobProposal->update([
                'proposal' => (string) $response,
                'status' => 'completed',
                'generated_at' => now(),
            ]);

            // Release semaphore if concurrency limiting is enabled
            if ((int) config('services.ai.max_parallel_requests', 0) > 0) {
                Redis::connection()->decr('generate_ai_proposal_semaphore');
            }

        } catch (\Exception $e) {
            // Release semaphore if concurrency limiting is enabled
            if ((int) config('services.ai.max_parallel_requests', 0) > 0) {
                Redis::connection()->decr('generate_ai_proposal_semaphore');
            }

            $this->aiJobProposal->update([
                'status' => 'failed',
                'proposal' => 'Error generating proposal: ' . $e->getMessage()
            ]);

            throw $e;
        }
           
    }

    public function reQueueJob(string $reason = 'unknown',$context = [])
    {
        Log::info('GenerateAiJobProposal requeued', [
            'reason' => $reason,
            'provider' => $this->aiJobProposal->provider,
            'ai_job_proposal_id' => $this->aiJobProposal->id,
            'context' => $context   
        ]);

        static::dispatch($this->aiJobProposal)->delay(now()->addSeconds(10));

        // Delete the current job instance so it doesn't "fail" or "retry"
        $this->delete();
    }
}
