<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Proposal for {{ $job->title }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }

        .markdown-body p { margin-bottom: 1rem; line-height: 1.6; }
        .markdown-body ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .markdown-body code { background: #f3f4f6; padding: 2px 4px; border-radius: 4px; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

<div class="max-w-4xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="bg-white p-6 rounded-xl shadow border mb-6">
        <h1 class="text-2xl font-bold mb-2">{{ $job->title }}</h1>
        <div class="text-sm text-gray-500">
            Proposal generated on {{ $proposal->generated_at?->format('Y-m-d H:i') ?? $proposal->created_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- Job Info -->
    @php
        $data = $job->getAdditonalData();
        $clientName = $data['node.job.ownership.team.name'] ?? 'N/A';
        $budget = $job->budget_minimum === $job->budget_maximum
            ? $job->budget_minimum . $job->getCurrencySymbol()
            : $job->budget_minimum . $job->getCurrencySymbol() . ' - ' . $job->budget_maximum . $job->getCurrencySymbol();
        $jobType = $job->is_hourly ? 'HOURLY' : 'FIXED RATE';
    @endphp

    <div class="bg-white p-6 rounded-xl shadow border mb-6">
        <h2 class="text-lg font-semibold mb-4">Job Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500 block mb-1">Client</span>
                <span class="font-medium">{{ $clientName }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Type</span>
                <span class="font-medium">{{ $jobType }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Budget</span>
                <span class="font-medium">{{ $budget }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Location</span>
                <span class="font-medium">{{ $job->location ?? 'N/A' }}</span>
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

                <div class="mt-6">
                    @if($proposal && $proposal->status === 'completed')

                    <!-- Success -->
                    <div id="completed-state">
                        <div id="proposal-content" class="markdown-body">
                            {{ $proposal->proposal  ?? 'No proposal content available.' }}
                        </div>
                        <script>
document.addEventListener("DOMContentLoaded", function() {

    const rawMarkdown = `{!! addslashes($proposal->proposal) !!}`;

    // Step 1: Convert Markdown → HTML
    const parsedHtml = marked.parse(rawMarkdown);

    // Step 2: Sanitize HTML
    const cleanHtml = DOMPurify.sanitize(parsedHtml);

    // Step 3: Inject into DOM
    document.getElementById('proposal-content').innerHTML = cleanHtml;

});
</script>

                        <div class="mt-4 text-right">
                            <button onclick="copyToClipboard()"
                                    class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">
                                <span id="copy-text">Copy</span>
                            </button>
                        </div>
                    </div>
                    @endif
                    @if($proposal && $proposal->status === 'generating')                                                                <!-- Loading -->
                    <div id="loading-state" class="text-center py-12">
                        <div class="loader mx-auto mb-4"></div>
                        <p id="loading-text" class="text-gray-500">Initializing AI...</p>
                    </div>
                    @endif
                    <!-- Error -->
                    <div id="error-state" class="text-center py-12">
                        <p id="error-message" class="text-red-500"></p>
                    </div>

                </div>
            </div>
        </details>
    </div>

    <!-- AI Configuration -->
    <div class="bg-white p-6 rounded-xl shadow border mb-6">
        <h2 class="text-lg font-semibold mb-4">AI Configuration</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 block mb-1">Model</span>
                <span class="font-medium">{{ $proposal->model }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Provider</span>
                <span class="font-medium capitalize">{{ $proposal->provider }}</span>
            </div>
            <div>
                <span class="text-gray-500 block mb-1">Conversation ID</span>
                <span class="font-medium">{{ $proposal->conversation_id }}</span>
            </div>
        </div>
    </div>

    <!-- Prompts & Instructions -->
    @if($proposal->status === 'completed')
    <div class="bg-white rounded-xl shadow border mb-6">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold">AI Prompts & Instructions</h2>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">System Prompt</h3>
                <pre class="bg-gray-50 p-4 rounded text-sm text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ $proposal->prompt }}</pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">AI Instructions</h3>
                <pre class="bg-gray-50 p-4 rounded text-sm text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ $proposal->instructions }}</pre>
            </div>
        </div>
    </div>
    @endif

    <div class="text-center text-xs text-gray-400">
        This is a temporary link valid for 24 hours. For full features including proposal regeneration, please log in.
    </div>

</div>

</body>
</html>
