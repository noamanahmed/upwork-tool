<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearPendingJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:clear-pending {connection=redis} {queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear pending, reserved, and ready jobs from a specified Redis queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $connection = $this->argument('connection');
        $queue = $this->argument('queue');

        if ($connection !== 'redis') {
            $this->error('Currently, only the Redis connection is supported.');
            return 1;
        }

        $this->clearRedisQueue($queue, 'delayed');
        $this->clearRedisQueue($queue, 'reserved');
        $this->clearRedisQueue($queue, 'ready');

        $this->info("Pending, reserved, and ready jobs cleared from the queue: {$queue}");

        return 0;
    }

    /**
     * Clear the pending jobs from the Redis queue.
     *
     * @param string $queue
     * @param string $type
     * @return void
     */
    protected function clearRedisQueue($queue, $type)
    {
        $queueName = "queues:{$queue}:{$type}";

        // Use Redis ZSCAN to find and delete all items in the sorted set
        $cursor = 0;
        do {
            list($cursor, $items) = Redis::zscan($queueName, $cursor);
            if ($items) {
                $memberKeys = array_keys($items);
                Redis::zrem($queueName, $memberKeys);
            }
        } while ($cursor != 0);

        $this->info("Cleared $cursor $type job(s) from the queue: {$queue}");

    }
}
