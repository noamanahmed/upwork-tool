<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal for {{ $job->title }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .markdown-body p {
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .markdown-body ul {
            list-style: disc;
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .markdown-body code {
            background: #f3f4f6;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .loader {
            border: 3px solid #eee;
            border-top: 3px solid #14a800;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Tab active state */
        .provider-tab-btn.active {
            border-bottom-color: #14a800;
            color: #14a800;
            font-weight: 600;
        }

        .provider-panel {
            display: none;
        }

        .provider-panel.active {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="max-w-5xl mx-auto px-4 py-8">

        <!-- Header -->
        <div class="bg-white p-6 rounded-xl shadow border mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold mb-2">{{ $job->title }}</h1>
                    <span class="text-sm text-gray-500">
                        Posted {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('jobs.index') }}"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-100 transition">
                        ← Back to Jobs
                    </a>
                    <a href="https://www.upwork.com/jobs/~{{ ltrim($job->ciphertext, '~') }}" target="_blank"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                        View on Upwork
                    </a>
                </div>
            </div>
        </div>

        <!-- Job Overview -->
        @php
            $data = $job->getAdditonalData();
            $clientName = $data['node.job.ownership.team.name'] ?? 'N/A';
            $budget = $job->budget_minimum === $job->budget_maximum
                ? $job->budget_minimum . $job->getCurrencySymbol()
                : $job->budget_minimum . $job->getCurrencySymbol() . ' - ' . $job->budget_maximum . $job->getCurrencySymbol();
            $jobType = $job->is_hourly ? 'HOURLY' : 'FIXED RATE';
            $projectTotalApplicants = $data['node.totalApplicants'] ?? 'N/A';
            $clientTotalHires = $data['node.client.totalHires'] ?? 'N/A';
            $clientTotalSpend = $data['node.client.totalSpent.rawValue'] ?? 'N/A';
            $clientTotalSpendCurrency = $data['node.client.totalSpent.currency'] ?? 'N/A';
            $clientTotalReviews = $data['node.client.totalReviews'] ?? 'N/A';
            $clientTotalFeedback = $data['node.client.totalFeedback'] ?? 'N/A';
            $clientTotalPostedJobs = $data['node.client.totalPostedJobs'] ?? 'N/A';
        @endphp

        <div class="bg-white p-6 rounded-xl shadow border mb-6">
            <h2 class="text-lg font-semibold mb-4">Job Overview</h2>
            <div class="grid grid-cols-[1fr_1fr_2fr] gap-2">
                <div>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><span class="text-gray-500 w-32 inline-block">Job Type:</span>
                            <strong class="text-gray-800">{{ $jobType }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Budget:</span>
                            <strong class="text-gray-800">{{ $budget }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Applicants:</span>
                            <strong class="text-gray-800">{{ $projectTotalApplicants }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Client Hires:</span>
                            <strong class="text-gray-800">{{ $clientTotalHires }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Client Feedback:</span>
                            <strong class="text-gray-800">{{ $clientTotalFeedback }}</strong>
                        </li>
                    </ul>
                </div>
                <div>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><span class="text-gray-500 w-32 inline-block">Client Name:</span>
                            <strong class="text-gray-800">{{ $clientName }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Location:</span>
                            <strong class="text-gray-800">{{ $job->location ?? 'N/A' }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Total Spend:</span>
                            <strong class="text-gray-800">{{ $clientTotalSpendCurrency }}
                                {{ $clientTotalSpend }}</strong>
                        </li>
                        <li><span class="text-gray-500 w-32 inline-block">Posted Jobs:</span>
                            <strong class="text-gray-800">{{ $clientTotalPostedJobs }}</strong>
                        </li>
                    </ul>
                </div>
                <div>
                    @php
                        $timeline = [
                            'Job Posted' => $job->created_at,
                            'Added to Platform' => $job->created_at,
                            'Queue Triggered' => $proposal?->created_at,
                            'Generated' => $proposal?->generated_at,
                        ];
                        $adminTimezone = config('admin.personal.timezone', 'UTC');
                    @endphp
                    <ul class="space-y-2 text-sm text-gray-600">
                        @foreach($timeline as $label => $date)
                            @php
                                $carbonDate = $date ? \Carbon\Carbon::parse($date)->timezone($adminTimezone) : null;
                            @endphp
                            <li><span class="text-gray-500 w-32 inline-block">{{ $label }}:</span>
                                <strong
                                    class="text-gray-800">{{ $carbonDate ? $carbonDate->format('Y-m-d H:i:s') . ' (' . $carbonDate->diffForHumans() . ')' : 'N/A' }}</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Job Description Accordion -->
        <div class="bg-white rounded-xl shadow border mb-6">
            <details class="group [&_summary::-webkit-details-marker]:hidden">
                <summary
                    class="flex items-center justify-between px-6 py-4 cursor-pointer list-none font-semibold bg-gray-50 hover:bg-gray-100 transition rounded-t-xl">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Job Description
                    </span>
                    <span class="transition group-open:rotate-180">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </span>
                </summary>
                <div class="px-6 py-4 border-t bg-white">
                    <div class="text-sm text-gray-700 whitespace-pre-wrap">
                        {{ $job->description }}
                    </div>
                </div>
            </details>
        </div>

        <!-- ===================== AI PROPOSALS (TABBED) ===================== -->
        <div class="bg-white rounded-xl shadow border mb-6 overflow-hidden">

            <!-- Tab Header -->
            <div class="flex border-b bg-gray-50">
                <div class="px-6 py-4 flex items-center gap-2 border-r">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="font-semibold text-sm text-gray-700">AI Proposals</span>
                </div>
                <div class="flex flex-1 overflow-x-auto" id="provider-tabs">
                    @foreach($enabledProviders as $providerKey)
                        @php
                            $isFirst = $loop->first;
                            $providerLabel = match ($providerKey) {
                                'openai' => 'OpenAI',
                                'gemini' => 'Gemini',
                                default => ucfirst($providerKey),
                            };
                            $providerStatus = $proposals->get($providerKey)?->status;
                            $badgeClass = match ($providerStatus) {
                                'completed' => 'bg-green-100 text-green-700',
                                'generating' => 'bg-yellow-100 text-yellow-700',
                                'failed' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-500',
                            };
                            $badgeText = match ($providerStatus) {
                                'completed' => 'Done',
                                'generating' => 'Generating',
                                'failed' => 'Failed',
                                default => 'Pending',
                            };
                        @endphp
                        <button id="tab-btn-{{ $providerKey }}"
                            class="provider-tab-btn px-5 py-4 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 transition-all flex items-center gap-2 whitespace-nowrap {{ $isFirst ? 'active' : '' }}"
                            onclick="switchTab('{{ $providerKey }}')">
                            {{ $providerLabel }}
                            <span id="tab-badge-{{ $providerKey }}"
                                class="px-2 py-0.5 text-xs rounded-full {{ $badgeClass }}">
                                {{ $badgeText }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Tab Panels -->
            @foreach($enabledProviders as $providerKey)
                @php
                    $isFirst = $loop->first;
                    $panelProposal = $proposals->get($providerKey);
                    $providerModel = config("services.ai.{$providerKey}.model", 'N/A');
                    $providerConversationId = config("services.ai.{$providerKey}.conversation_id", 'N/A');
                @endphp
                <div id="panel-{{ $providerKey }}" class="provider-panel {{ $isFirst ? 'active' : '' }}">

                    <!-- Panel toolbar -->
                    <div class="px-6 py-3 border-b bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span>Model: <strong
                                    class="text-gray-700">{{ $panelProposal?->model ?? $providerModel }}</strong></span>
                            <span class="text-gray-300">|</span>
                            <span>Conv: <strong
                                    class="text-gray-700">{{ $panelProposal?->conversation_id ?? $providerConversationId }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="status-badge-{{ $providerKey }}" class="px-3 py-1 text-xs rounded bg-gray-200">
                                Initializing...
                            </span>
                            <button id="regenerate-btn-{{ $providerKey }}"
                                onclick="regenerateProposal('{{ $providerKey }}')"
                                class="hidden px-3 py-1 text-sm border rounded hover:bg-gray-100 transition">
                                ↺ Regenerate
                            </button>
                        </div>
                    </div>

                    <!-- Panel content -->
                    <div class="p-6">
                        <!-- Loading -->
                        <div id="loading-state-{{ $providerKey }}" class="text-center py-12">
                            <div class="loader mx-auto mb-4"></div>
                            <p id="loading-text-{{ $providerKey }}" class="text-gray-500">Initializing AI...</p>
                        </div>

                        <!-- Error -->
                        <div id="error-state-{{ $providerKey }}" class="hidden text-center py-12">
                            <p id="error-message-{{ $providerKey }}" class="text-red-500"></p>
                        </div>

                        <!-- Success -->
                        <div id="completed-state-{{ $providerKey }}" class="hidden">
                            <div id="proposal-content-{{ $providerKey }}" class="markdown-body"></div>
                            <div class="mt-4 text-right">
                                <button onclick="copyToClipboard('{{ $providerKey }}')"
                                    class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">
                                    <span id="copy-text-{{ $providerKey }}">Copy</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Prompts & Instructions accordion (per provider) -->
                    @if($panelProposal)
                        <div class="border-t">
                            <details class="group [&_summary::-webkit-details-marker]:hidden">
                                <summary
                                    class="flex items-center justify-between px-6 py-3 cursor-pointer list-none bg-gray-50 hover:bg-gray-100 transition text-sm">
                                    <span class="flex items-center gap-2 font-medium text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                            </path>
                                        </svg>
                                        Prompts & Instructions
                                    </span>
                                    <span class="transition group-open:rotate-180">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </span>
                                </summary>
                                <div class="px-6 py-4 border-t bg-white space-y-4">
                                    <div>
                                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">System Prompt</h3>
                                        <pre
                                            class="bg-gray-50 p-4 rounded text-sm text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ $panelProposal->prompt ?? 'N/A' }}</pre>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">AI Instructions</h3>
                                        <pre
                                            class="bg-gray-50 p-4 rounded text-sm text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ $panelProposal->instructions ?? 'N/A' }}</pre>
                                    </div>
                                </div>
                            </details>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

    </div>

    <script>
        const jobId = "{{ $job->id }}";
        const enabledProviders = @json($enabledProviders);

        // Per-provider state
        const state = {};
        enabledProviders.forEach(p => {
            state[p] = {
                proposalId: null,
                pollInterval: null,
                pollAttempts: 0,
                rawText: null,
            };
        });

        // -------- TAB SWITCHING --------

        function switchTab(provider) {
            enabledProviders.forEach(p => {
                document.getElementById('tab-btn-' + p)?.classList.remove('active');
                document.getElementById('panel-' + p)?.classList.remove('active');
            });
            document.getElementById('tab-btn-' + provider)?.classList.add('active');
            document.getElementById('panel-' + provider)?.classList.add('active');
        }

        // -------- INIT --------

        document.addEventListener('DOMContentLoaded', () => {
            @foreach($enabledProviders as $providerKey)
                @php $panelProposal = $proposals->get($providerKey); @endphp
                @if($panelProposal && $panelProposal->status === 'completed')
                    showProposal('{{ $providerKey }}', @json($panelProposal->proposal));
                @elseif($panelProposal && $panelProposal->status === 'generating')
                    // Already generating — jump straight into polling
                    state['{{ $providerKey }}'].proposalId = {{ $panelProposal->id }};
                    showLoading('{{ $providerKey }}', 'Generating...');
                    updateStatus('{{ $providerKey }}', 'Generating...', 'bg-yellow-100 text-yellow-700');
                    startPolling('{{ $providerKey }}');
                @elseif($panelProposal && $panelProposal->status === 'failed')
                    showError('{{ $providerKey }}', 'Previous generation failed. Click Regenerate to retry.');
                @else
                    startGeneration('{{ $providerKey }}');
                @endif
            @endforeach
});

        // -------- STATE DISPLAY --------

        function showLoading(provider, text = 'Generating...') {
            document.getElementById('loading-state-' + provider).classList.remove('hidden');
            document.getElementById('error-state-' + provider).classList.add('hidden');
            document.getElementById('completed-state-' + provider).classList.add('hidden');
            document.getElementById('loading-text-' + provider).innerText = text;
            document.getElementById('regenerate-btn-' + provider).classList.add('hidden');
        }

        function showError(provider, message) {
            document.getElementById('loading-state-' + provider).classList.add('hidden');
            document.getElementById('completed-state-' + provider).classList.add('hidden');
            document.getElementById('error-state-' + provider).classList.remove('hidden');
            document.getElementById('error-message-' + provider).innerText = message;
            document.getElementById('regenerate-btn-' + provider).classList.remove('hidden');
            updateStatus(provider, 'Failed', 'bg-red-100 text-red-700');
            updateTabBadge(provider, 'Failed', 'bg-red-100 text-red-700');
        }

        function showProposal(provider, markdown) {
            document.getElementById('loading-state-' + provider).classList.add('hidden');
            document.getElementById('error-state-' + provider).classList.add('hidden');
            document.getElementById('completed-state-' + provider).classList.remove('hidden');
            document.getElementById('proposal-content-' + provider).innerHTML =
                DOMPurify.sanitize(marked.parse(markdown));
            document.getElementById('regenerate-btn-' + provider).classList.remove('hidden');
            state[provider].rawText = markdown;
            updateStatus(provider, 'Completed', 'bg-green-100 text-green-700');
            updateTabBadge(provider, 'Done', 'bg-green-100 text-green-700');
        }

        // -------- GENERATION FLOW --------

        function startGeneration(provider) {
            showLoading(provider, 'Requesting AI...');
            updateStatus(provider, 'Queued', 'bg-blue-100 text-blue-700');
            updateTabBadge(provider, 'Queued', 'bg-blue-100 text-blue-700');

            fetch(`/api/v1/upwork/job/${jobId}/generate-proposal?provider=${provider}`)
                .then(handleResponse)
                .then(data => {
                    if (!data.proposal) throw new Error('No proposal in response');

                    state[provider].proposalId = data.proposal.id;

                    if (data.proposal.status === 'completed') {
                        showProposal(provider, data.proposal.proposal);
                    } else {
                        updateStatus(provider, 'Generating...', 'bg-yellow-100 text-yellow-700');
                        updateTabBadge(provider, 'Generating', 'bg-yellow-100 text-yellow-700');
                        startPolling(provider);
                    }
                })
                .catch(err => showError(provider, 'Failed to start generation: ' + err.message));
        }

        function startPolling(provider) {
            if (!state[provider].proposalId) return;

            const MAX_POLL = 40;
            state[provider].pollAttempts = 0;

            state[provider].pollInterval = setInterval(() => {
                if (state[provider].pollAttempts++ > MAX_POLL) {
                    clearInterval(state[provider].pollInterval);
                    showError(provider, 'Timeout waiting for proposal.');
                    return;
                }

                fetch(`/api/v1/upwork/job/${jobId}/${state[provider].proposalId}`)
                    .then(handleResponse)
                    .then(data => {
                        if (!data.proposal) return;

                        if (data.proposal.status === 'completed') {
                            clearInterval(state[provider].pollInterval);
                            showProposal(provider, data.proposal.proposal);
                        }

                        if (data.proposal.status === 'failed') {
                            clearInterval(state[provider].pollInterval);
                            showError(provider, data.proposal.proposal || 'Generation failed.');
                        }
                    })
                    .catch(() => {/* silently retry */ });

            }, 3000);
        }

        // -------- REGENERATE --------

        function regenerateProposal(provider) {
            if (!confirm(`Regenerate proposal using ${provider}?`)) return;

            // Stop any in-flight polling
            if (state[provider].pollInterval) {
                clearInterval(state[provider].pollInterval);
                state[provider].pollInterval = null;
            }

            showLoading(provider, 'Regenerating...');
            updateStatus(provider, 'Queued', 'bg-blue-100 text-blue-700');
            updateTabBadge(provider, 'Queued', 'bg-blue-100 text-blue-700');

            fetch(`/api/v1/upwork/job/${jobId}/regenerate-proposal?provider=${provider}`, { method: 'POST' })
                .then(handleResponse)
                .then(data => {
                    if (!data.proposal) throw new Error('No proposal in response');
                    state[provider].proposalId = data.proposal.id;
                    updateStatus(provider, 'Generating...', 'bg-yellow-100 text-yellow-700');
                    updateTabBadge(provider, 'Generating', 'bg-yellow-100 text-yellow-700');
                    startPolling(provider);
                })
                .catch(err => {
                    showError(provider, 'Regeneration failed: ' + err.message);
                });
        }

        // -------- COPY --------

        function copyToClipboard(provider) {
            navigator.clipboard.writeText(state[provider].rawText || '').then(() => {
                const el = document.getElementById('copy-text-' + provider);
                el.innerText = 'Copied!';
                el.classList.add('text-green-600');
                setTimeout(() => {
                    el.innerText = 'Copy';
                    el.classList.remove('text-green-600');
                }, 2000);
            });
        }

        // -------- HELPERS --------

        function updateStatus(provider, text, classes) {
            const el = document.getElementById('status-badge-' + provider);
            if (!el) return;
            el.innerText = text;
            el.className = `px-3 py-1 text-xs rounded ${classes}`;
        }

        function updateTabBadge(provider, text, classes) {
            const el = document.getElementById('tab-badge-' + provider);
            if (!el) return;
            el.innerText = text;
            el.className = `px-2 py-0.5 text-xs rounded-full ${classes}`;
        }

        function handleResponse(res) {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        }
    </script>

</body>

</html>