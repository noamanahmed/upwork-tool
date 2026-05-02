<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal for {{ $job->title }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        upwork: {
                            DEFAULT: '#14a800',
                            dark: '#108a00',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Marked.js for markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .markdown-body h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .markdown-body h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .markdown-body p {
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .markdown-body ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .markdown-body ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #14a800;
            width: 24px;
            height: 24px;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased min-h-screen">

    <div class="max-w-5xl mx-auto px-4 py-8">

        <!-- Header / Job Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
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

            <div class="flex flex-col md:flex-row justify-between md:items-start gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight mb-2">
                        {{ $job->title }}
                    </h1>
                    <div class="flex items-center text-sm text-gray-500 gap-4">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Posted {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="https://www.upwork.com/jobs/~{{ $job->ciphertext }}" target="_blank"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-upwork hover:bg-upwork-dark transition shadow-sm">
                        View on UpWork
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Job Overview -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-3 border-b border-gray-200 pb-2">Job Overview</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><span class="text-gray-500 inline-block w-32">Job Type:</span> <strong
                                class="text-gray-800">{{ $jobType }}</strong></li>
                        <li><span class="text-gray-500 inline-block w-32">Budget:</span> <strong
                                class="text-gray-800">{{ $budget }}</strong></li>
                        <li><span class="text-gray-500 inline-block w-32">Applicants:</span> <strong
                                class="text-gray-800">{{ $projectTotalApplicants }}</strong></li>
                    </ul>
                </div>

                <!-- Client Details -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-3 border-b border-gray-200 pb-2">Client Details</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><span class="text-gray-500 inline-block w-32">Client Name:</span> <strong
                                class="text-gray-800">{{ $clientName }}</strong></li>
                        <li><span class="text-gray-500 inline-block w-32">Location:</span> <strong
                                class="text-gray-800">{{ $job->location ?? 'N/A' }}</strong></li>
                        <li><span class="text-gray-500 inline-block w-32">Total Spend:</span> <strong
                                class="text-gray-800">{{ $clientTotalSpendCurrency }} {{ $clientTotalSpend }}</strong>
                        </li>
                        <li><span class="text-gray-500 inline-block w-32">Metrics:</span> <span
                                class="text-gray-800">{{ $clientTotalHires }} Hires &middot;
                                {{ $clientTotalPostedJobs }} Jobs &middot; {{ $clientTotalReviews }} Reviews</span></li>
                    </ul>
                </div>
            </div>

            <!-- Accordion for Job Description -->
            <details
                class="group bg-white rounded-lg border border-gray-200 shadow-sm [&_summary::-webkit-details-marker]:hidden">
                <summary
                    class="flex items-center justify-between p-4 cursor-pointer list-none font-medium text-gray-900 bg-gray-50 hover:bg-gray-100 transition rounded-lg group-open:rounded-b-none">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7"></path>
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
                <div
                    class="px-5 py-4 border-t border-gray-200 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
                    {!! e($job->description) !!}
                </div>
            </details>
        </div>

        <!-- Proposal Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[300px]">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-upwork" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    AI Generated Proposal
                </h2>
                <div id="status-badge"
                    class="px-3 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-600 shadow-inner">
                    Initializing...
                </div>
            </div>

            <div class="p-6">
                <!-- Generating State -->
                <div id="loading-state" class="flex flex-col mx-auto items-center justify-center py-12 text-center">
                    <div class="loader mb-4"></div>
                    <h3 class="text-gray-900 font-medium text-lg mb-1">Crafting the perfect proposal...</h3>
                    <p class="text-gray-500 text-sm max-w-sm">Our AI agent is currently generating a highly tailored
                        proposal based on your freelancer profile. This usually takes 10-20 seconds.</p>
                </div>

                <!-- Error State -->
                <div id="error-state" class="hidden flex-col mx-auto items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-gray-900 font-medium text-lg mb-1">Generation Failed</h3>
                    <p id="error-message" class="text-red-500 text-sm max-w-sm"></p>
                </div>

                <!-- Completed State -->
                <div id="completed-state" class="hidden">
                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                        <div id="proposal-content" class="markdown-body text-gray-800"></div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button onclick="copyToClipboard()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span id="copy-text">Copy Proposal</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const jobId = "{{ $job->id }}";
        const apiUrl = `/api/v1/upwork/job/${jobId}/generate-proposal`;
        let proposalId = null;
        let pollInterval = null;

        document.addEventListener('DOMContentLoaded', () => {
            startGeneration();
        });

        function startGeneration() {
            updateStatus('Requesting', 'bg-blue-100 text-blue-700');

            fetch(apiUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.data?.proposal) {
                        const proposal = data.data.proposal;
                        proposalId = proposal.id;

                        if (proposal.status === 'completed') {
                            showProposal(proposal.proposal);
                        } else if (proposal.status === 'failed') {
                            showError(proposal.proposal || "The generation job failed internally.");
                        } else {
                            // Generating / Pending
                            updateStatus('Generating...', 'bg-yellow-100 text-yellow-700');
                            startPolling();
                        }
                    } else {
                        showError("Failed to initialize proposal generation.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showError("Network error while requesting proposal.");
                });
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);

            pollInterval = setInterval(() => {
                fetch(`/api/v1/upwork/job/${jobId}/${proposalId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.data?.proposal) {
                            const proposal = data.data.proposal;

                            if (proposal.status === 'completed') {
                                clearInterval(pollInterval);
                                showProposal(proposal.proposal);
                            } else if (proposal.status === 'failed') {
                                clearInterval(pollInterval);
                                showError(proposal.proposal || "Generation failed.");
                            }
                        }
                    })
                    .catch(err => console.error("Polling error", err));
            }, 3000); // Check every 3 seconds
        }

        function showProposal(markdownText) {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('error-state').classList.add('hidden');
            document.getElementById('completed-state').classList.remove('hidden');

            updateStatus('Completed', 'bg-green-100 text-green-700');

            // Parse Markdown
            document.getElementById('proposal-content').innerHTML = marked.parse(markdownText);

            // For copying
            window.rawProposalText = markdownText;
        }

        function showError(message) {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('completed-state').classList.add('hidden');
            document.getElementById('error-state').classList.remove('hidden');

            updateStatus('Failed', 'bg-red-100 text-red-700');
            document.getElementById('error-message').innerText = message;
        }

        function updateStatus(text, classes) {
            const el = document.getElementById('status-badge');
            el.innerText = text;
            el.className = `px-3 py-1 text-xs font-medium rounded-full shadow-inner ${classes}`;
        }

        function copyToClipboard() {
            if (window.rawProposalText) {
                navigator.clipboard.writeText(window.rawProposalText).then(() => {
                    const btnText = document.getElementById('copy-text');
                    const oldText = btnText.innerText;
                    btnText.innerText = "Copied!";
                    setTimeout(() => {
                        btnText.innerText = oldText;
                    }, 2000);
                });
            }
        }
    </script>
</body>

</html>