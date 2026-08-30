<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>

    <div class="space-y-6">
        <!-- Header Controls & Upwork Link -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $job->title }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Posted {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }} (ID: {{ $job->upwork_id }})
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-arrow-left"
                    tag="a"
                    href="{{ \App\Filament\Resources\JobResource::getUrl('index') }}">
                    Back to Jobs
                </x-filament::button>

                @if($job->ciphertext)
                    <x-filament::button
                        color="success"
                        icon="heroicon-o-arrow-top-right-on-square"
                        tag="a"
                        target="_blank"
                        href="https://www.upwork.com/jobs/~{{ ltrim($job->ciphertext, '~') }}">
                        View on Upwork
                    </x-filament::button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{
            activeTab: '{{ $visibleTabs->first() ?? 'gemini' }}',
            copied: false,
            copyContent(elementId) {
                const text = document.getElementById(elementId)?.innerText || '';
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                });
            }
        }">
            <!-- Left Column: Job Details Card -->
            <div class="lg:col-span-1 space-y-4">
                <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-3">
                        Job Overview
                    </h3>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Budget</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                @if($job->budget_minimum === $job->budget_maximum)
                                    ${{ number_format($job->budget_minimum) }}
                                @else
                                    ${{ number_format($job->budget_minimum) }} - ${{ number_format($job->budget_maximum) }}
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Type</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $job->is_hourly ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                {{ $job->is_hourly ? 'Hourly' : 'Fixed Rate' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Location</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $job->location ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Payment Verified</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $job->is_payment_verified ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $job->is_payment_verified ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>

                    <!-- Client Stats -->
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-800 space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Client Info</h4>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                <span class="text-gray-500 dark:text-gray-400 block">Total Spend</span>
                                <span class="font-bold text-gray-900 dark:text-white">${{ number_format((float) $job->client_total_spent) }} {{ $job->client_total_spent_currency }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                <span class="text-gray-500 dark:text-gray-400 block">Total Hires</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ number_format((int) $job->client_total_hires) }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                <span class="text-gray-500 dark:text-gray-400 block">Feedback</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $job->client_total_feedback ? number_format($job->client_total_feedback, 1) . ' ★' : 'N/A' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                <span class="text-gray-500 dark:text-gray-400 block">Posted Jobs</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ number_format((int) $job->client_total_posted_jobs) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-800">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Description</h4>
                        <div class="text-xs text-gray-700 dark:text-gray-300 max-h-60 overflow-y-auto whitespace-pre-line leading-relaxed bg-gray-50 dark:bg-gray-800 p-3 rounded">
                            {{ $job->description }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: AI Proposals Tabbed Interface -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <!-- Provider Tabs Header -->
                    <div class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-2 flex flex-wrap gap-2 overflow-x-auto">
                        @foreach($visibleTabs as $provider)
                            @php
                                $prop = $proposals->get($provider);
                                $status = $prop->status ?? 'not_generated';
                            @endphp
                            <button
                                type="button"
                                @click="activeTab = '{{ $provider }}'"
                                :class="activeTab === '{{ $provider }}'
                                    ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 font-bold shadow-sm border-gray-200 dark:border-gray-700'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800'"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium border border-transparent transition flex items-center gap-1.5">
                                <span>{{ ucfirst($provider) }}</span>
                                @if($status === 'completed')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                @elseif($status === 'pending')
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                @elseif($status === 'failed')
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <!-- Provider Proposal Panels -->
                    <div class="p-6">
                        @foreach($visibleTabs as $provider)
                            @php
                                $prop = $proposals->get($provider);
                            @endphp
                            <div x-show="activeTab === '{{ $provider }}'" x-cloak class="space-y-4">
                                @if($prop)
                                    <!-- Proposal Header Info -->
                                    <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 dark:text-white uppercase">{{ $provider }}</span>
                                            @if($prop->model)
                                                <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded font-mono">{{ $prop->model }}</span>
                                            @endif
                                            <span class="px-2 py-0.5 rounded font-semibold uppercase {{ $prop->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : ($prop->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200') }}">
                                                {{ $prop->status }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            @if($prop->generated_at)
                                                <span class="text-gray-500 dark:text-gray-400">Generated {{ \Carbon\Carbon::parse($prop->generated_at)->diffForHumans() }}</span>
                                            @endif

                                            <button
                                                type="button"
                                                @click="copyContent('raw-proposal-{{ $provider }}')"
                                                class="flex items-center gap-1 px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white rounded font-medium transition shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="copied ? 'Copied!' : 'Copy Proposal'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Rendered Proposal Markdown -->
                                    <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg min-h-[300px]"
                                         id="rendered-proposal-{{ $provider }}"
                                         x-init="
                                            const raw = document.getElementById('raw-proposal-{{ $provider }}')?.innerText || '';
                                            $el.innerHTML = DOMPurify.sanitize(marked.parse(raw));
                                         ">
                                    </div>

                                    <!-- Hidden Raw Proposal Text for Copy -->
                                    <div class="hidden" id="raw-proposal-{{ $provider }}">{{ $prop->proposal }}</div>

                                    <!-- Error output if failed -->
                                    @if($prop->error_message)
                                        <div class="p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-lg text-xs text-red-700 dark:text-red-300">
                                            <strong>Error:</strong> {{ $prop->error_message }}
                                        </div>
                                    @endif
                                @else
                                    <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                                        <p class="text-sm">No proposal generated yet for provider <strong>{{ $provider }}</strong>.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
