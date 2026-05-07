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
        body { font-family: 'Inter', sans-serif; }

        .markdown-body p { margin-bottom: 1rem; line-height: 1.6; }
        .markdown-body ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .markdown-body code { background: #f3f4f6; padding: 2px 4px; border-radius: 4px; }

        .loader {
            border: 3px solid #eee;
            border-top: 3px solid #14a800;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        .timeline-card {
            @apply bg-gray-50 p-3 rounded border border-gray-100;
        }
        .timeline-label {
            @apply text-xs text-gray-500 mb-1 block;
        }
        .timeline-value {
            @apply text-sm font-medium text-gray-800;
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
                <a href="https://www.upwork.com/jobs/~{{ $job->ciphertext }}"
                   target="_blank"
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
                        <strong class="text-gray-800">{{ $clientTotalSpendCurrency }} {{ $clientTotalSpend }}</strong>
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
            @endphp
            <ul class="space-y-2 text-sm text-gray-600">
            @foreach($timeline as $label => $date)                
                    <li><span class="text-gray-500 w-32 inline-block">{{ $label }}:</span>
                        <strong class="text-gray-800">{{ $date ? \Carbon\Carbon::parse($date)->format('Y-m-d H:i') . ' (' . \Carbon\Carbon::parse($date)->diffForHumans() . ')' : 'N/A' }}</strong>
                    </li>
                @endforeach
                </ul>
            </div>
        </div>
    </div>



    <!-- Job Description Accordion -->
    <div class="bg-white rounded-xl shadow border mb-6">
        <details class="group [&_summary::-webkit-details-marker]:hidden">
            <summary class="flex items-center justify-between px-6 py-4 cursor-pointer list-none font-semibold bg-gray-50 hover:bg-gray-100 transition rounded-t-xl">
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

    <!-- AI Proposal Accordion -->
    @if($proposal)
    <div class="bg-white rounded-xl shadow border overflow-hidden mb-6">
        <details class="group [&_summary::-webkit-details-marker]:hidden" open>
            <summary class="flex items-center justify-between px-6 py-4 cursor-pointer list-none font-semibold bg-gray-50 hover:bg-gray-100 transition rounded-t-xl">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-upwork" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    AI Proposal
                </span>
                <span class="transition group-open:rotate-180">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </span>
            </summary>
            <div class="p-6 bg-white">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50 -mx-6 -mt-6">
                    <h2 class="font-semibold">Current Proposal</h2>
                    <div class="flex items-center gap-3">
                        <div id="status-badge" class="px-3 py-1 text-xs rounded bg-gray-200">
                            Initializing...
                        </div>
                        @if($proposal && $proposal->status === 'completed')
                            <button id="regenerate-btn"
                                    onclick="regenerateProposal()"
                                    class="px-3 py-1 text-sm border rounded hover:bg-gray-100">
                                Regenerate
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <!-- Loading -->
                    <div id="loading-state" class="text-center py-12">
                        <div class="loader mx-auto mb-4"></div>
                        <p id="loading-text" class="text-gray-500">Initializing AI...</p>
                    </div>

                    <!-- Error -->
                    <div id="error-state" class="hidden text-center py-12">
                        <p id="error-message" class="text-red-500"></p>
                    </div>

                    <!-- Success -->
                    <div id="completed-state" class="hidden">
                        <div id="proposal-content" class="markdown-body"></div>

                        <div class="mt-4 text-right">
                            <button onclick="copyToClipboard()"
                                    class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">
                                <span id="copy-text">Copy</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </details>
    </div>
    @endif


    <!-- AI Configuration -->
    <div class="bg-white p-6 rounded-xl shadow border mb-6">
        <h2 class="text-lg font-semibold mb-4">AI Configuration</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 block mb-1">Model</span>
                <span class="font-medium">{{ $proposal?->model ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Provider</span>
                <span class="font-medium capitalize">{{ $proposal?->provider ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Conversation ID</span>
                <span class="font-medium">{{ $proposal?->conversation_id ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- AI Prompts & Instructions Accordion -->
    @if($proposal)
    <div class="bg-white rounded-xl shadow border mb-6">
        <details class="group [&_summary::-webkit-details-marker]:hidden">
            <summary class="flex items-center justify-between px-6 py-4 cursor-pointer list-none font-semibold bg-gray-50 hover:bg-gray-100 transition rounded-t-xl">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                        </path>
                    </svg>
                    AI Prompts & Instructions
                </span>
                <span class="transition group-open:rotate-180">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </span>
            </summary>
            <div class="px-6 py-4 border-t bg-white space-y-6">
                <!-- Prompt -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">System Prompt</h3>
                    <pre class="bg-gray-50 p-4 rounded text-sm text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ $proposal->prompt ?? 'N/A' }}</pre>
                </div>

                <!-- Instructions -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">AI Instructions</h3>
                    <pre class="bg-gray-50 p-4 rounded text-sm text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ $proposal->instructions ?? 'N/A' }}</pre>
                </div>
            </div>
        </details>
    </div>
    @endif



</div>

<script>
const jobId = "{{ $job->id }}";
const apiUrl = `/api/v1/upwork/job/${jobId}/generate-proposal`;
const regenerateUrl = `/api/v1/upwork/job/${jobId}/regenerate-proposal`;

let proposalId = null;
let pollInterval = null;
let pollAttempts = 0;
const MAX_POLL = 40;

document.addEventListener('DOMContentLoaded', () => {
    @if($proposal && $proposal->status === 'completed')
        showProposal(@json($proposal->proposal));
    @else
        startGeneration();
    @endif
});

/* ---------------------- STATES ---------------------- */

function showLoading(text = 'Generating...') {
    document.getElementById('loading-state').classList.remove('hidden');
    document.getElementById('error-state').classList.add('hidden');
    document.getElementById('completed-state').classList.add('hidden');
    document.getElementById('loading-text').innerText = text;
}

function showError(message) {
    showLoading('');
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-state').classList.remove('hidden');
    document.getElementById('error-message').innerText = message;
    updateStatus('Failed', 'bg-red-100 text-red-700');
}

function showProposal(markdown) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-state').classList.add('hidden');
    document.getElementById('completed-state').classList.remove('hidden');

    document.getElementById('proposal-content').innerHTML =
        DOMPurify.sanitize(marked.parse(markdown));

    updateStatus('Completed', 'bg-green-100 text-green-700');
    window.rawProposalText = markdown;

    // Show regenerate button
    const btn = document.getElementById('regenerate-btn');
    if (btn) btn.classList.remove('hidden');
}

function showError(message) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('completed-state').classList.add('hidden');
    document.getElementById('error-state').classList.remove('hidden');

    updateStatus('Failed', 'bg-red-100 text-red-700');
    document.getElementById('error-message').innerText = message;

    // Show regenerate button for retry
    const btn = document.getElementById('regenerate-btn');
    if (btn) btn.classList.remove('hidden');
}

/* ---------------------- FLOW ---------------------- */

function startGeneration() {
    // Hide regenerate button during generation
    const btn = document.getElementById('regenerate-btn');
    if (btn) btn.classList.add('hidden');

    showLoading('Requesting AI...');
    updateStatus('Queued', 'bg-blue-100 text-blue-700');

    fetch(apiUrl)
        .then(handleResponse)
        .then(data => {
            if (!data.proposal) throw new Error();

            proposalId = data.proposal.id;

            if (data.proposal.status === 'completed') {
                showProposal(data.proposal.proposal);
            } else {
                updateStatus('Generating...', 'bg-yellow-100 text-yellow-700');
                startPolling();
            }
        })
        .catch(() => showError("Failed to start generation."));
}

function startPolling() {
    if (!proposalId) return;

    pollInterval = setInterval(() => {

        if (pollAttempts++ > MAX_POLL) {
            clearInterval(pollInterval);
            showError("Timeout waiting for proposal.");
            return;
        }

        fetch(`/api/v1/upwork/job/${jobId}/${proposalId}`)
            .then(handleResponse)
            .then(data => {
                if (!data.proposal) return;

                if (data.proposal.status === 'completed') {
                    clearInterval(pollInterval);
                    showProposal(data.proposal.proposal);
                }

                if (data.proposal.status === 'failed') {
                    clearInterval(pollInterval);
                    showError(data.proposal.proposal || 'Failed.');
                }
            });

    }, 3000);
}

/* ---------------------- ACTIONS ---------------------- */

function regenerateProposal() {
    if (!confirm('Regenerate proposal?')) return;

    // Hide regenerate button during regeneration
    const btn = document.getElementById('regenerate-btn');
    if (btn) btn.classList.add('hidden');

    showLoading('Regenerating...');
    updateStatus('Queued', 'bg-blue-100 text-blue-700');

    fetch(regenerateUrl, { method: 'POST' })
        .then(handleResponse)
        .then(data => {
            if (!data.success) throw new Error();

            proposalId = data.proposal.id;
            startPolling();
        })
        .catch(() => {
            showError("Regeneration failed.");
            // Show button again on error
            if (btn) btn.classList.remove('hidden');
        });
}

function copyToClipboard() {
    navigator.clipboard.writeText(window.rawProposalText || '')
        .then(() => {
            const el = document.getElementById('copy-text');
            el.innerText = 'Copied!';
            el.classList.add('text-green-600');

            setTimeout(() => {
                el.innerText = 'Copy';
                el.classList.remove('text-green-600');
            }, 2000);
        });
}

/* ---------------------- HELPERS ---------------------- */

function updateStatus(text, classes) {
    const el = document.getElementById('status-badge');
    el.innerText = text;
    el.className = `px-3 py-1 text-xs rounded ${classes}`;
}

function handleResponse(res) {
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}
</script>

</body>
</html>
