<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class GenerateAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:generate {period=all : The period to generate (daily, monthly, yearly, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Upwork analytics for a given period (daily, monthly, yearly, all)';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsService $service): int
    {
        $period = strtolower($this->argument('period'));

        if ($period === 'all') {
            $this->call('analytics:daily');
            $this->line('');
            $this->call('analytics:monthly');
            $this->line('');
            $this->call('analytics:yearly');
            return Command::SUCCESS;
        }

        switch ($period) {
            case 'daily':
                return $this->call('analytics:daily');
            case 'monthly':
                return $this->call('analytics:monthly');
            case 'yearly':
                return $this->call('analytics:yearly');
            default:
                $this->error("Invalid period '{$period}'. Allowed periods: daily, monthly, yearly, all.");
                return Command::FAILURE;
        }
    }
}
