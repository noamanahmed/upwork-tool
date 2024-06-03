<?php

namespace App\Console\Commands;

use App\Models\TelescopeEntry;
use Illuminate\Console\Command;

class UpworkPruneTelescope extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:prune-telescope';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timestamp = now()->subHours(24);
        TelescopeEntry::whereType('cache')->where('created_at',$timestamp)->delete();
        TelescopeEntry::whereType('query')->where('created_at',$timestamp)->delete();
    }
}
