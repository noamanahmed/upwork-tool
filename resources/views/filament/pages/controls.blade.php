<x-filament-panels::page>
    <div class="space-y-6">

        <!-- 1. Primary Analytics Generators Section -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Analytics Generation Controls</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manually trigger background console commands to calculate and cache Upwork analytics snapshots.</p>
                </div>

                <x-filament::button 
                    wire:click="triggerAllAnalytics"
                    wire:loading.attr="disabled"
                    icon="heroicon-o-sparkles" 
                    color="gray"
                    size="sm">
                    <span wire:loading.remove wire:target="triggerAllAnalytics">Generate All (Daily + Monthly + Yearly)</span>
                    <span wire:loading wire:target="triggerAllAnalytics">Generating All...</span>
                </x-filament::button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Daily Analytics Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2.5 bg-blue-50 dark:bg-blue-950/50 rounded-lg text-blue-600 dark:text-blue-400">
                                <x-heroicon-o-calendar class="w-6 h-6" />
                            </div>
                            @if (!empty($dailyAnalytics['generated_at']))
                                <span class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-400 font-medium rounded-full">
                                    {{ \Carbon\Carbon::parse($dailyAnalytics['generated_at'])->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full">Not Generated</span>
                            @endif
                        </div>

                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Daily Analytics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Aggregates jobs fetched, hourly vs fixed rates, client spend, and AI proposals across the last 24 hours.
                        </p>

                        @if (!empty($dailyAnalytics['metrics']))
                            <div class="grid grid-cols-2 gap-2 text-xs py-2 border-t border-gray-100 dark:border-gray-800 mb-4">
                                <div><span class="text-gray-400">Jobs:</span> <strong class="text-gray-800 dark:text-gray-200">{{ number_format($dailyAnalytics['metrics']['total_jobs']) }}</strong></div>
                                <div><span class="text-gray-400">Spend:</span> <strong class="text-gray-800 dark:text-gray-200">${{ number_format($dailyAnalytics['metrics']['total_client_spent']) }}</strong></div>
                                <div><span class="text-gray-400">AI Props:</span> <strong class="text-gray-800 dark:text-gray-200">{{ $dailyAnalytics['metrics']['total_ai_proposals'] }}</strong></div>
                                <div><span class="text-gray-400">Rating:</span> <strong class="text-gray-800 dark:text-gray-200">★ {{ $dailyAnalytics['metrics']['avg_client_feedback'] }}</strong></div>
                            </div>
                        @endif
                    </div>

                    <x-filament::button 
                        wire:click="triggerDailyAnalytics"
                        wire:loading.attr="disabled"
                        color="primary"
                        icon="heroicon-o-play"
                        class="w-full">
                        <span wire:loading.remove wire:target="triggerDailyAnalytics">Generate Daily Analytics</span>
                        <span wire:loading wire:target="triggerDailyAnalytics">Running Command...</span>
                    </x-filament::button>
                </div>

                <!-- Monthly Analytics Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/50 rounded-lg text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-o-calendar-days class="w-6 h-6" />
                            </div>
                            @if (!empty($monthlyAnalytics['generated_at']))
                                <span class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-400 font-medium rounded-full">
                                    {{ \Carbon\Carbon::parse($monthlyAnalytics['generated_at'])->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full">Not Generated</span>
                            @endif
                        </div>

                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Monthly Analytics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Calculates monthly totals, growth trends vs previous month, client hire volume, and top skills.
                        </p>

                        @if (!empty($monthlyAnalytics['metrics']))
                            <div class="grid grid-cols-2 gap-2 text-xs py-2 border-t border-gray-100 dark:border-gray-800 mb-4">
                                <div><span class="text-gray-400">Jobs:</span> <strong class="text-gray-800 dark:text-gray-200">{{ number_format($monthlyAnalytics['metrics']['total_jobs']) }}</strong></div>
                                <div><span class="text-gray-400">Spend:</span> <strong class="text-gray-800 dark:text-gray-200">${{ number_format($monthlyAnalytics['metrics']['total_client_spent']) }}</strong></div>
                                <div><span class="text-gray-400">Growth:</span> <strong class="text-gray-800 dark:text-gray-200">{{ $monthlyAnalytics['metrics']['job_growth_percent'] }}%</strong></div>
                                <div><span class="text-gray-400">AI Props:</span> <strong class="text-gray-800 dark:text-gray-200">{{ $monthlyAnalytics['metrics']['total_ai_proposals'] }}</strong></div>
                            </div>
                        @endif
                    </div>

                    <x-filament::button 
                        wire:click="triggerMonthlyAnalytics"
                        wire:loading.attr="disabled"
                        color="success"
                        icon="heroicon-o-play"
                        class="w-full">
                        <span wire:loading.remove wire:target="triggerMonthlyAnalytics">Generate Monthly Analytics</span>
                        <span wire:loading wire:target="triggerMonthlyAnalytics">Running Command...</span>
                    </x-filament::button>
                </div>

                <!-- Yearly Analytics Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2.5 bg-amber-50 dark:bg-amber-950/50 rounded-lg text-amber-600 dark:text-amber-400">
                                <x-heroicon-o-chart-bar class="w-6 h-6" />
                            </div>
                            @if (!empty($yearlyAnalytics['generated_at']))
                                <span class="text-xs px-2.5 py-1 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-400 font-medium rounded-full">
                                    {{ \Carbon\Carbon::parse($yearlyAnalytics['generated_at'])->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full">Not Generated</span>
                            @endif
                        </div>

                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Yearly Analytics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Calculates comprehensive year-to-date metrics, market trends, search breakdown, and demand statistics.
                        </p>

                        @if (!empty($yearlyAnalytics['metrics']))
                            <div class="grid grid-cols-2 gap-2 text-xs py-2 border-t border-gray-100 dark:border-gray-800 mb-4">
                                <div><span class="text-gray-400">Jobs:</span> <strong class="text-gray-800 dark:text-gray-200">{{ number_format($yearlyAnalytics['metrics']['total_jobs']) }}</strong></div>
                                <div><span class="text-gray-400">Spend:</span> <strong class="text-gray-800 dark:text-gray-200">${{ number_format($yearlyAnalytics['metrics']['total_client_spent']) }}</strong></div>
                                <div><span class="text-gray-400">Growth:</span> <strong class="text-gray-800 dark:text-gray-200">{{ $yearlyAnalytics['metrics']['job_growth_percent'] }}%</strong></div>
                                <div><span class="text-gray-400">AI Props:</span> <strong class="text-gray-800 dark:text-gray-200">{{ $yearlyAnalytics['metrics']['total_ai_proposals'] }}</strong></div>
                            </div>
                        @endif
                    </div>

                    <x-filament::button 
                        wire:click="triggerYearlyAnalytics"
                        wire:loading.attr="disabled"
                        color="warning"
                        icon="heroicon-o-play"
                        class="w-full">
                        <span wire:loading.remove wire:target="triggerYearlyAnalytics">Generate Yearly Analytics</span>
                        <span wire:loading wire:target="triggerYearlyAnalytics">Running Command...</span>
                    </x-filament::button>
                </div>

            </div>
        </div>

        <!-- 2. System Debug Controls (Secondary Command Triggers) -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Scheduled Tasks Debug Triggers</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Manually dispatch or test background artisan commands on-demand without waiting for the cron timer.</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <x-filament::button 
                    wire:click="triggerJobSearch" 
                    wire:loading.attr="disabled"
                    color="gray" 
                    icon="heroicon-o-magnifying-glass"
                    size="sm">
                    <span wire:loading.remove wire:target="triggerJobSearch">Dispatch Job Searches</span>
                    <span wire:loading wire:target="triggerJobSearch">Dispatching...</span>
                </x-filament::button>

                <x-filament::button 
                    wire:click="triggerSlackNotifications" 
                    wire:loading.attr="disabled"
                    color="gray" 
                    icon="heroicon-o-bell"
                    size="sm">
                    <span wire:loading.remove wire:target="triggerSlackNotifications">Send Slack Alerts</span>
                    <span wire:loading wire:target="triggerSlackNotifications">Sending...</span>
                </x-filament::button>

                <x-filament::button 
                    wire:click="triggerAiProposals" 
                    wire:loading.attr="disabled"
                    color="gray" 
                    icon="heroicon-o-document-text"
                    size="sm">
                    <span wire:loading.remove wire:target="triggerAiProposals">Generate AI Proposals</span>
                    <span wire:loading wire:target="triggerAiProposals">Running...</span>
                </x-filament::button>

                <x-filament::button 
                    wire:click="triggerPruneTelescope" 
                    wire:loading.attr="disabled"
                    color="gray" 
                    icon="heroicon-o-trash"
                    size="sm">
                    <span wire:loading.remove wire:target="triggerPruneTelescope">Prune Telescope</span>
                    <span wire:loading wire:target="triggerPruneTelescope">Pruning...</span>
                </x-filament::button>
            </div>
        </div>

        <!-- 3. Terminal Output / Console Log (if any command was executed) -->
        @if ($lastCommandOutput)
            <div class="bg-gray-950 rounded-xl border border-gray-800 p-4 shadow-inner">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-800 text-xs text-gray-400">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="font-mono">Console Output (Command: {{ $lastExecutedAction }})</span>
                    </div>
                    <span>{{ now()->format('H:i:s') }}</span>
                </div>
                <pre class="font-mono text-xs text-gray-300 whitespace-pre-wrap overflow-x-auto max-h-64">{{ $lastCommandOutput }}</pre>
            </div>
        @endif

        <!-- 4. Analytics Details & Inspection Section -->
        @php
            $currentData = match($activeTab) {
                'monthly' => $monthlyAnalytics,
                'yearly' => $yearlyAnalytics,
                default => $dailyAnalytics,
            };
        @endphp

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <!-- Period Selector Tabs -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800 mb-5">
                <div class="flex items-center gap-2">
                    <button 
                        type="button"
                        wire:click="setTab('daily')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition {{ $activeTab === 'daily' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        Daily View
                    </button>
                    <button 
                        type="button"
                        wire:click="setTab('monthly')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition {{ $activeTab === 'monthly' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        Monthly View
                    </button>
                    <button 
                        type="button"
                        wire:click="setTab('yearly')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition {{ $activeTab === 'yearly' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        Yearly View
                    </button>
                </div>

                @if (!empty($currentData['generated_at']))
                    <span class="text-xs text-gray-500">
                        Snapshot generated on <strong>{{ \Carbon\Carbon::parse($currentData['generated_at'])->format('M d, Y H:i:s') }}</strong>
                    </span>
                @endif
            </div>

            @if (empty($currentData['metrics']))
                <div class="py-12 text-center">
                    <div class="inline-flex p-3 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-400 mb-3">
                        <x-heroicon-o-chart-bar class="w-6 h-6" />
                    </div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">No {{ ucfirst($activeTab) }} Analytics Snapshot Yet</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                        Click the "Generate {{ ucfirst($activeTab) }} Analytics" button above to run the command and generate the report.
                    </p>
                </div>
            @else
                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Jobs Ingested</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($currentData['metrics']['total_jobs']) }}</div>
                        <div class="text-xs text-gray-400 mt-1">Growth: <span class="{{ $currentData['metrics']['job_growth_percent'] >= 0 ? 'text-green-600' : 'text-red-500' }} font-medium">{{ $currentData['metrics']['job_growth_percent'] }}%</span></div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Hourly vs Fixed</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $currentData['metrics']['hourly_jobs'] }} / {{ $currentData['metrics']['fixed_jobs'] }}</div>
                        <div class="text-xs text-gray-400 mt-1">Avg Hourly: ${{ number_format($currentData['metrics']['avg_hourly_rate'], 1) }}/hr</div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Client Total Spend</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($currentData['metrics']['total_client_spent']) }}</div>
                        <div class="text-xs text-gray-400 mt-1">Hires: {{ number_format($currentData['metrics']['total_client_hires']) }} | ★ {{ $currentData['metrics']['avg_client_feedback'] }}</div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">AI Proposals Generated</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($currentData['metrics']['total_ai_proposals']) }}</div>
                        <div class="text-xs text-gray-400 mt-1">Verified Pay: {{ $currentData['metrics']['verified_payment_percentage'] }}%</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jobs per Search Table -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Jobs Matched by Search Configuration</h4>
                        @if (!empty($currentData['searches_breakdown']))
                            <div class="overflow-x-auto border border-gray-200 dark:border-gray-800 rounded-lg">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800">
                                        <tr>
                                            <th class="px-3.5 py-2 font-semibold">Search Category</th>
                                            <th class="px-3.5 py-2 font-semibold text-right">Jobs Found</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($currentData['searches_breakdown'] as $search)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                <td class="px-3.5 py-2 text-gray-800 dark:text-gray-200 font-medium">{{ $search['search_name'] }}</td>
                                                <td class="px-3.5 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($search['jobs_count']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">No search breakdown data available for this period.</p>
                        @endif
                    </div>

                    <!-- Top Skills -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Top In-Demand Skills</h4>
                        @if (!empty($currentData['top_skills']))
                            <div class="flex flex-wrap gap-2">
                                @foreach ($currentData['top_skills'] as $skillName => $skillCount)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-medium">
                                        <span>{{ $skillName }}</span>
                                        <span class="px-1.5 py-0.5 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[10px] font-bold">
                                            {{ $skillCount }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">No skills data recorded in this period.</p>
                        @endif

                        @if (!empty($currentData['metrics']['ai_by_provider']))
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mt-6 mb-3">AI Proposals by Provider</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($currentData['metrics']['ai_by_provider'] as $provider => $count)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-xs font-medium">
                                        <span class="uppercase">{{ $provider }}</span>
                                        <span class="px-1.5 py-0.5 rounded-full bg-white dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-[10px] font-bold">
                                            {{ $count }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
