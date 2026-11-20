<x-filament-panels::page>
    <div class="space-y-6">

        <!-- 1. System Health & Control Header Bar -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800 p-6 text-white shadow-lg border border-gray-800">
            <!-- Background Decorative Glow -->
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-primary-600/20 blur-3xl pointer-events-none"></div>
            <div class="absolute right-48 -bottom-20 h-48 w-48 rounded-full bg-blue-600/15 blur-2xl pointer-events-none"></div>

            <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="p-2 bg-primary-500/20 text-primary-400 rounded-lg ring-1 ring-primary-500/30">
                            <x-heroicon-o-command-line class="w-6 h-6" />
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-white">Operations & Debug Console</h2>
                    </div>
                    <p class="text-sm text-gray-400 max-w-2xl leading-relaxed">
                        Trigger manual pipeline operations, execute Upwork scheduled commands, generate analytical snapshots, and monitor live system status on demand.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <x-filament::button 
                        wire:click="refreshAll" 
                        wire:loading.attr="disabled"
                        color="gray"
                        size="sm"
                        icon="heroicon-o-arrow-path">
                        <span wire:loading.remove wire:target="refreshAll">Refresh Status</span>
                        <span wire:loading wire:target="refreshAll">Refreshing...</span>
                    </x-filament::button>

                    <x-filament::button 
                        wire:click="triggerAllAnalytics"
                        wire:loading.attr="disabled"
                        icon="heroicon-o-sparkles" 
                        color="primary"
                        size="sm">
                        <span wire:loading.remove wire:target="triggerAllAnalytics">Generate All Analytics</span>
                        <span wire:loading wire:target="triggerAllAnalytics">Generating...</span>
                    </x-filament::button>
                </div>
            </div>

            <!-- Live Status Indicator Badges -->
            <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 pt-5 border-t border-gray-800/80">
                <!-- Scheduler Status -->
                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                    <span class="relative flex h-3 w-3">
                        @if ($isCronEnabled)
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        @endif
                    </span>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Scheduler</div>
                        <div class="text-xs font-semibold text-gray-200">
                            {{ $isCronEnabled ? 'Active (' . $cronWindow . ')' : 'Paused (Disabled)' }}
                        </div>
                    </div>
                </div>

                <!-- Active Searches -->
                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                    <div class="p-1.5 bg-blue-500/10 text-blue-400 rounded-lg">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Active Searches</div>
                        <div class="text-xs font-semibold text-gray-200">
                            {{ $activeSearchesCount }} Active Target Queries
                        </div>
                    </div>
                </div>

                <!-- Total Ingested Jobs -->
                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                    <div class="p-1.5 bg-emerald-500/10 text-emerald-400 rounded-lg">
                        <x-heroicon-o-briefcase class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Total Indexed Jobs</div>
                        <div class="text-xs font-semibold text-gray-200">
                            {{ number_format($totalJobsCount) }} Discovered Jobs
                        </div>
                    </div>
                </div>

                <!-- Primary AI Provider -->
                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                    <div class="p-1.5 bg-purple-500/10 text-purple-400 rounded-lg">
                        <x-heroicon-o-cpu-chip class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Default AI Provider</div>
                        <div class="text-xs font-semibold text-purple-300 uppercase">
                            {{ $currentAiProvider }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Analytics Generation Command Hub -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-chart-pie class="w-5 h-5 text-primary-500" />
                        Analytics Generation Hub
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Calculate, aggregate, and persist analytical snapshots across daily, monthly, and yearly windows.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Daily Analytics Card -->
                <div class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-blue-50 dark:bg-blue-950/60 rounded-xl text-blue-600 dark:text-blue-400 ring-1 ring-blue-500/20">
                                    <x-heroicon-o-calendar class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Daily Snapshot</h4>
                                    <span class="text-[11px] text-gray-400 font-mono">analytics:daily</span>
                                </div>
                            </div>

                            @if (!empty($dailyAnalytics['generated_at']))
                                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-medium rounded-full ring-1 ring-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ \Carbon\Carbon::parse($dailyAnalytics['generated_at'])->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full font-medium">Unseeded</span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5 leading-relaxed">
                            Aggregates new postings, hourly vs fixed rates, total client spend, and AI proposals across the last 24 hours.
                        </p>

                        @if (!empty($dailyAnalytics['metrics']))
                            <div class="grid grid-cols-2 gap-2 text-xs p-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-800 mb-5">
                                <div class="space-y-0.5">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Jobs Ingested</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($dailyAnalytics['metrics']['total_jobs']) }}</div>
                                </div>
                                <div class="space-y-0.5">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Client Spend</div>
                                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($dailyAnalytics['metrics']['total_client_spent']) }}</div>
                                </div>
                                <div class="space-y-0.5 pt-2 border-t border-gray-200/40 dark:border-gray-700/40">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">AI Proposals</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $dailyAnalytics['metrics']['total_ai_proposals'] }}</div>
                                </div>
                                <div class="space-y-0.5 pt-2 border-t border-gray-200/40 dark:border-gray-700/40">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Client Rating</div>
                                    <div class="text-sm font-bold text-amber-500">★ {{ $dailyAnalytics['metrics']['avg_client_feedback'] }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <x-filament::button 
                        wire:click="triggerDailyAnalytics"
                        wire:loading.attr="disabled"
                        color="primary"
                        icon="heroicon-o-play"
                        class="w-full justify-center">
                        <span wire:loading.remove wire:target="triggerDailyAnalytics">Run Daily Generator</span>
                        <span wire:loading wire:target="triggerDailyAnalytics">Processing...</span>
                    </x-filament::button>
                </div>

                <!-- Monthly Analytics Card -->
                <div class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/60 rounded-xl text-emerald-600 dark:text-emerald-400 ring-1 ring-emerald-500/20">
                                    <x-heroicon-o-calendar-days class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Monthly Snapshot</h4>
                                    <span class="text-[11px] text-gray-400 font-mono">analytics:monthly</span>
                                </div>
                            </div>

                            @if (!empty($monthlyAnalytics['generated_at']))
                                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-medium rounded-full ring-1 ring-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ \Carbon\Carbon::parse($monthlyAnalytics['generated_at'])->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full font-medium">Unseeded</span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5 leading-relaxed">
                            Calculates monthly totals, growth trend percentage vs preceding month, client hire frequency, and top skills.
                        </p>

                        @if (!empty($monthlyAnalytics['metrics']))
                            <div class="grid grid-cols-2 gap-2 text-xs p-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-800 mb-5">
                                <div class="space-y-0.5">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Jobs Ingested</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($monthlyAnalytics['metrics']['total_jobs']) }}</div>
                                </div>
                                <div class="space-y-0.5">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Client Spend</div>
                                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($monthlyAnalytics['metrics']['total_client_spent']) }}</div>
                                </div>
                                <div class="space-y-0.5 pt-2 border-t border-gray-200/40 dark:border-gray-700/40">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Growth vs Prior</div>
                                    <div class="text-sm font-bold {{ $monthlyAnalytics['metrics']['job_growth_percent'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                        {{ $monthlyAnalytics['metrics']['job_growth_percent'] }}%
                                    </div>
                                </div>
                                <div class="space-y-0.5 pt-2 border-t border-gray-200/40 dark:border-gray-700/40">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">AI Proposals</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $monthlyAnalytics['metrics']['total_ai_proposals'] }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <x-filament::button 
                        wire:click="triggerMonthlyAnalytics"
                        wire:loading.attr="disabled"
                        color="success"
                        icon="heroicon-o-play"
                        class="w-full justify-center">
                        <span wire:loading.remove wire:target="triggerMonthlyAnalytics">Run Monthly Generator</span>
                        <span wire:loading wire:target="triggerMonthlyAnalytics">Processing...</span>
                    </x-filament::button>
                </div>

                <!-- Yearly Analytics Card -->
                <div class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-amber-50 dark:bg-amber-950/60 rounded-xl text-amber-600 dark:text-amber-400 ring-1 ring-amber-500/20">
                                    <x-heroicon-o-chart-bar class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Yearly Snapshot</h4>
                                    <span class="text-[11px] text-gray-400 font-mono">analytics:yearly</span>
                                </div>
                            </div>

                            @if (!empty($yearlyAnalytics['generated_at']))
                                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-medium rounded-full ring-1 ring-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ \Carbon\Carbon::parse($yearlyAnalytics['generated_at'])->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full font-medium">Unseeded</span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5 leading-relaxed">
                            Aggregates year-to-date volume, yearly growth, search category distribution, and market-wide skill demand.
                        </p>

                        @if (!empty($yearlyAnalytics['metrics']))
                            <div class="grid grid-cols-2 gap-2 text-xs p-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-800 mb-5">
                                <div class="space-y-0.5">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Jobs Ingested</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($yearlyAnalytics['metrics']['total_jobs']) }}</div>
                                </div>
                                <div class="space-y-0.5">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Client Spend</div>
                                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($yearlyAnalytics['metrics']['total_client_spent']) }}</div>
                                </div>
                                <div class="space-y-0.5 pt-2 border-t border-gray-200/40 dark:border-gray-700/40">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">Growth vs Prior</div>
                                    <div class="text-sm font-bold {{ $yearlyAnalytics['metrics']['job_growth_percent'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                        {{ $yearlyAnalytics['metrics']['job_growth_percent'] }}%
                                    </div>
                                </div>
                                <div class="space-y-0.5 pt-2 border-t border-gray-200/40 dark:border-gray-700/40">
                                    <div class="text-[11px] text-gray-400 uppercase tracking-wider">AI Proposals</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $yearlyAnalytics['metrics']['total_ai_proposals'] }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <x-filament::button 
                        wire:click="triggerYearlyAnalytics"
                        wire:loading.attr="disabled"
                        color="warning"
                        icon="heroicon-o-play"
                        class="w-full justify-center">
                        <span wire:loading.remove wire:target="triggerYearlyAnalytics">Run Yearly Generator</span>
                        <span wire:loading wire:target="triggerYearlyAnalytics">Processing...</span>
                    </x-filament::button>
                </div>

            </div>
        </div>

        <!-- 3. Scheduled Tasks & Maintenance Control Center -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-5">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-bolt class="w-5 h-5 text-amber-500" />
                        Pipeline Actions & Background Commands
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dispatch individual background commands immediately for testing, scraping, AI generation, or system maintenance.</p>
                </div>
            </div>

            <!-- Pipeline Operations -->
            <div class="space-y-5">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 block mb-3">Upwork Pipeline Triggers</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        
                        <!-- 1. upwork:search -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-primary-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500/10 text-blue-500 rounded-lg">
                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Run Job Discovery</div>
                                    <div class="text-[11px] text-gray-400 font-mono">upwork:search</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerJobSearch" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="gray">
                                <span wire:loading.remove wire:target="triggerJobSearch">Dispatch</span>
                                <span wire:loading wire:target="triggerJobSearch">...</span>
                            </x-filament::button>
                        </div>

                        <!-- 2. upwork:send-slack-notifications -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-primary-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg">
                                    <x-heroicon-o-bell class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Send Slack Alerts</div>
                                    <div class="text-[11px] text-gray-400 font-mono">upwork:send-slack...</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerSlackNotifications" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="gray">
                                <span wire:loading.remove wire:target="triggerSlackNotifications">Send</span>
                                <span wire:loading wire:target="triggerSlackNotifications">...</span>
                            </x-filament::button>
                        </div>

                        <!-- 3. upwork:generate-ai-proposals -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-primary-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-500/10 text-purple-500 rounded-lg">
                                    <x-heroicon-o-sparkles class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Generate AI Proposals</div>
                                    <div class="text-[11px] text-gray-400 font-mono">upwork:generate-ai...</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerAiProposals" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="gray">
                                <span wire:loading.remove wire:target="triggerAiProposals">Generate</span>
                                <span wire:loading wire:target="triggerAiProposals">...</span>
                            </x-filament::button>
                        </div>

                        <!-- 4. upwork:proposals -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-primary-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-500/10 text-amber-500 rounded-lg">
                                    <x-heroicon-o-document-check class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Sync Proposal Statuses</div>
                                    <div class="text-[11px] text-gray-400 font-mono">upwork:proposals</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerFetchProposals" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="gray">
                                <span wire:loading.remove wire:target="triggerFetchProposals">Sync</span>
                                <span wire:loading wire:target="triggerFetchProposals">...</span>
                            </x-filament::button>
                        </div>

                        <!-- 5. upwork:proposal-jobs -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-primary-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-indigo-500/10 text-indigo-500 rounded-lg">
                                    <x-heroicon-o-link class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Backfill Proposal Jobs</div>
                                    <div class="text-[11px] text-gray-400 font-mono">upwork:proposal-jobs</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerFetchProposalJobs" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="gray">
                                <span wire:loading.remove wire:target="triggerFetchProposalJobs">Backfill</span>
                                <span wire:loading wire:target="triggerFetchProposalJobs">...</span>
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Operations -->
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 block mb-3">System & Cache Maintenance</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        
                        <!-- Clear Cache -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-emerald-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg">
                                    <x-heroicon-o-arrow-path class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Flush System & Setting Cache</div>
                                    <div class="text-[11px] text-gray-400 font-mono">cache:clear + Setting::clearCache()</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerClearCache" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="success">
                                <span wire:loading.remove wire:target="triggerClearCache">Flush</span>
                                <span wire:loading wire:target="triggerClearCache">...</span>
                            </x-filament::button>
                        </div>

                        <!-- Prune Telescope -->
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-800 hover:border-rose-500/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-rose-500/10 text-rose-500 rounded-lg">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-white">Prune Telescope Entries (&gt;24h)</div>
                                    <div class="text-[11px] text-gray-400 font-mono">upwork:prune-telescope</div>
                                </div>
                            </div>
                            <x-filament::button 
                                wire:click="triggerPruneTelescope" 
                                wire:loading.attr="disabled"
                                size="xs"
                                color="danger">
                                <span wire:loading.remove wire:target="triggerPruneTelescope">Prune</span>
                                <span wire:loading wire:target="triggerPruneTelescope">...</span>
                            </x-filament::button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Real-time Command Output Console -->
        @if ($lastCommandOutput)
            <div class="rounded-2xl border border-gray-800 bg-gray-950 shadow-2xl overflow-hidden">
                <!-- Mac Terminal Header Bar -->
                <div class="flex items-center justify-between px-4 py-3 bg-gray-900/90 border-b border-gray-800 text-xs">
                    <div class="flex items-center gap-2">
                        <!-- macOS window dots -->
                        <div class="flex items-center gap-1.5 mr-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                        </div>
                        <span class="font-mono text-gray-400 text-xs flex items-center gap-1.5">
                            <span class="text-primary-400 font-semibold">$ php artisan</span>
                            <span class="text-gray-200 font-bold">{{ $lastExecutedAction }}</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-[11px] text-gray-400 font-mono">
                            {{ now()->format('H:i:s') }}
                        </span>
                        <button 
                            type="button"
                            wire:click="clearTerminalOutput"
                            class="text-[11px] text-gray-400 hover:text-gray-200 px-2 py-0.5 rounded bg-gray-800 hover:bg-gray-700 transition font-mono">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Command Output Area -->
                <div class="p-4 font-mono text-xs text-gray-300 whitespace-pre-wrap overflow-x-auto max-h-72 leading-relaxed selection:bg-primary-500 selection:text-white">
{{ $lastCommandOutput }}
                </div>
            </div>
        @endif

        <!-- 5. Analytics Explorer & Detailed Inspection -->
        @php
            $currentData = match($activeTab) {
                'monthly' => $monthlyAnalytics,
                'yearly' => $yearlyAnalytics,
                default => $dailyAnalytics,
            };
        @endphp

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <!-- Period Selector Tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-5 border-b border-gray-200 dark:border-gray-800 gap-4 mb-6">
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-xl gap-1">
                    <button 
                        type="button"
                        wire:click="setTab('daily')"
                        class="flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition {{ $activeTab === 'daily' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-calendar class="w-4 h-4" />
                        Daily Window
                    </button>
                    <button 
                        type="button"
                        wire:click="setTab('monthly')"
                        class="flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition {{ $activeTab === 'monthly' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-calendar-days class="w-4 h-4" />
                        Monthly Window
                    </button>
                    <button 
                        type="button"
                        wire:click="setTab('yearly')"
                        class="flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition {{ $activeTab === 'yearly' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-chart-bar class="w-4 h-4" />
                        Yearly Window
                    </button>
                </div>

                @if (!empty($currentData['generated_at']))
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                        Snapshot generated on <strong class="text-gray-700 dark:text-gray-300 font-mono">{{ \Carbon\Carbon::parse($currentData['generated_at'])->format('M d, Y H:i:s') }}</strong>
                    </div>
                @endif
            </div>

            @if (empty($currentData['metrics']))
                <div class="py-16 text-center">
                    <div class="inline-flex p-4 bg-gray-100 dark:bg-gray-800/80 rounded-2xl text-gray-400 mb-4 ring-1 ring-gray-200 dark:ring-gray-700">
                        <x-heroicon-o-chart-bar class="w-8 h-8" />
                    </div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">No {{ ucfirst($activeTab) }} Analytics Snapshot Cached</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto leading-relaxed">
                        No report data exists for this interval yet. Click the generator button above to run the command and generate the report snapshot.
                    </p>
                </div>
            @else
                <!-- 4 KPI Metrics Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Metric 1: Total Jobs -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jobs Discovered</span>
                            <div class="p-2 bg-blue-500/10 text-blue-500 rounded-lg">
                                <x-heroicon-o-briefcase class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($currentData['metrics']['total_jobs']) }}</div>
                        <div class="flex items-center gap-1.5 mt-2 text-xs">
                            <span class="font-semibold {{ $currentData['metrics']['job_growth_percent'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                {{ $currentData['metrics']['job_growth_percent'] >= 0 ? '+' : '' }}{{ $currentData['metrics']['job_growth_percent'] }}%
                            </span>
                            <span class="text-gray-400">vs prior period</span>
                        </div>
                    </div>

                    <!-- Metric 2: Hourly vs Fixed -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hourly / Fixed Ratio</span>
                            <div class="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg">
                                <x-heroicon-o-banknotes class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">
                            {{ $currentData['metrics']['hourly_jobs'] }} <span class="text-gray-400 text-lg font-normal">/</span> {{ $currentData['metrics']['fixed_jobs'] }}
                        </div>
                        <div class="text-xs text-gray-400 mt-2">
                            Avg Rate: <strong class="text-gray-700 dark:text-gray-300 font-semibold">${{ number_format($currentData['metrics']['avg_hourly_rate'], 1) }}/hr</strong>
                        </div>
                    </div>

                    <!-- Metric 3: Total Client Spend -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client Cumulative Spend</span>
                            <div class="p-2 bg-amber-500/10 text-amber-500 rounded-lg">
                                <x-heroicon-o-currency-dollar class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($currentData['metrics']['total_client_spent']) }}
                        </div>
                        <div class="text-xs text-gray-400 mt-2">
                            Total Hires: <strong class="text-gray-700 dark:text-gray-300">{{ number_format($currentData['metrics']['total_client_hires']) }}</strong> | ★ {{ $currentData['metrics']['avg_client_feedback'] }}
                        </div>
                    </div>

                    <!-- Metric 4: AI Proposals -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">AI Proposals Generated</span>
                            <div class="p-2 bg-purple-500/10 text-purple-500 rounded-lg">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($currentData['metrics']['total_ai_proposals']) }}</div>
                        <div class="text-xs text-gray-400 mt-2">
                            Verified Payment: <strong class="text-gray-700 dark:text-gray-300">{{ $currentData['metrics']['verified_payment_percentage'] }}%</strong>
                        </div>
                    </div>
                </div>

                <!-- Deep Dive: Searches Breakdown & Skills Cloud -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Column 1: Search Breakdown -->
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jobs by Search Query</h4>
                            <span class="text-xs text-gray-400 font-medium">{{ count($currentData['searches_breakdown'] ?? []) }} Searches</span>
                        </div>

                        @if (!empty($currentData['searches_breakdown']))
                            @php
                                $maxCount = max(array_column($currentData['searches_breakdown'], 'jobs_count') ?: [1]);
                            @endphp
                            <div class="space-y-2.5">
                                @foreach ($currentData['searches_breakdown'] as $index => $search)
                                    @php
                                        $percent = $maxCount > 0 ? round(($search['jobs_count'] / $maxCount) * 100) : 0;
                                    @endphp
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
                                        <div class="flex items-center justify-between text-xs mb-1.5">
                                            <span class="font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                                <span class="text-[10px] w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center font-bold">
                                                    {{ $index + 1 }}
                                                </span>
                                                {{ $search['search_name'] }}
                                            </span>
                                            <span class="font-bold text-gray-900 dark:text-white font-mono">
                                                {{ number_format($search['jobs_count']) }} jobs
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-primary-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                                <p class="text-xs text-gray-400 italic">No search breakdown records in this snapshot.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Column 2: In-Demand Skills & Provider Stats -->
                    <div class="space-y-6">
                        <!-- Top Skills Cloud -->
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Top In-Demand Skills</h4>
                                <span class="text-xs text-gray-400 font-medium">{{ count($currentData['top_skills'] ?? []) }} Identified</span>
                            </div>

                            @if (!empty($currentData['top_skills']))
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($currentData['top_skills'] as $skillName => $skillCount)
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200/60 dark:border-gray-700/60 text-gray-800 dark:text-gray-200 text-xs font-medium hover:border-primary-500 transition">
                                            <span>{{ $skillName }}</span>
                                            <span class="px-1.5 py-0.5 rounded-full bg-primary-500/10 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 text-[10px] font-bold">
                                                {{ $skillCount }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                                    <p class="text-xs text-gray-400 italic">No skill tags extracted in this snapshot.</p>
                                </div>
                            @endif
                        </div>

                        <!-- AI Proposals Distribution -->
                        @if (!empty($currentData['metrics']['ai_by_provider']))
                            <div class="pt-5 border-t border-gray-100 dark:border-gray-800">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3.5">AI Proposal Volume by Provider</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach ($currentData['metrics']['ai_by_provider'] as $provider => $count)
                                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
                                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ $provider }}</div>
                                            <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($count) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
