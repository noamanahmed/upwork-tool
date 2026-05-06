<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

<div class="max-w-full mx-auto px-4 py-8">

    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Jobs</h1>
        <div class="flex gap-2">
            <a href="{{ route('jobs.index', ['per_page' => 15]) }}"
               class="px-3 py-1 text-sm border rounded {{ request()->get('per_page') == 15 ? 'bg-gray-200' : 'bg-white' }}">15</a>
            <a href="{{ route('jobs.index', ['per_page' => 50]) }}"
               class="px-3 py-1 text-sm border rounded {{ request()->get('per_page') == 50 ? 'bg-gray-200' : 'bg-white' }}">50</a>
            <a href="{{ route('jobs.index', ['per_page' => 100]) }}"
               class="px-3 py-1 text-sm border rounded {{ request()->get('per_page') == 100 ? 'bg-gray-200' : 'bg-white' }}">100</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                    <tr>
                        @php
                            $headers = [
                                ['label' => 'Job Title', 'sort' => 'title'],
                                ['label' => 'Status', 'sort' => 'proposal_status'],
                                ['label' => 'Type', 'sort' => 'is_hourly'],
                                ['label' => 'Budget', 'sort' => 'budget_minimum'],
                                ['label' => 'Applicants', 'sort' => 'applicants'],
                                ['label' => 'Client', 'sort' => 'client_name'],
                                ['label' => 'Location', 'sort' => 'location'],
                                ['label' => 'Spend', 'sort' => 'total_spend'],
                                ['label' => 'Posted Jobs', 'sort' => 'client_total_posted_jobs'],
                                ['label' => 'Hires', 'sort' => 'client_total_hires'],
                                ['label' => 'Reviews', 'sort' => 'client_total_reviews'],
                                ['label' => 'Feedback', 'sort' => 'client_total_feedback'],
                                ['label' => 'Posted', 'sort' => 'created_at'],
                            ];
                        @endphp

                        @foreach($headers as $header)
                            @php
                                $isSorted = $sortBy === $header['sort'];
                                $nextDir = $isSorted && $sortDir === 'asc' ? 'desc' : 'asc';
                                $icon = $isSorted ? ($sortDir === 'asc' ? '↑' : '↓') : '↕';
                                $query = array_merge(request()->query(), ['sort' => $header['sort'], 'dir' => $nextDir]);
                            @endphp
                            <th class="px-4 py-3">
                                <a href="{{ route('jobs.index', $query) }}"
                                   class="flex items-center gap-1 hover:text-gray-900 transition"
                                   title="Sort by {{ $header['label'] }}">
                                    <span>{{ $header['label'] }}</span>
                                    <span class="text-xs">{{ $icon }}</span>
                                </a>
                            </th>
                        @endforeach

                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($jobs as $job)
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

                            $latestProposal = $job->aiProposals->sortByDesc('created_at')->first();
                            $proposalStatus = $latestProposal?->status ?? 'none';
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($job->title, 50) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClass = match($proposalStatus) {
                                        'completed' => 'bg-green-100 text-green-700',
                                        'failed' => 'bg-red-100 text-red-700',
                                        'generating' => 'bg-yellow-100 text-yellow-700',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                    $statusLabel = match($proposalStatus) {
                                        'completed' => 'Done',
                                        'failed' => 'Failed',
                                        'generating' => 'Generating',
                                        default => 'None'
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs rounded {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $jobType }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $budget }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $projectTotalApplicants }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $clientName }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $job->location ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">
                                    {{ $clientTotalSpendCurrency }} {{ $clientTotalSpend }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $clientTotalPostedJobs }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $clientTotalHires }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $clientTotalReviews }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-700">{{ $clientTotalFeedback }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-500 text-xs">
                                    {{ $job->created_at->format('Y-m-d H:i') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="https://www.upwork.com/jobs/~{{ $job->ciphertext }}"
                                       target="_blank"
                                       class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition"
                                       title="View on Upwork">
                                        Upwork
                                    </a>
                                    <a href="{{ route('job.proposal', ['jobId' => $job->id]) }}"
                                       class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                                       title="View Proposal">
                                        Proposal
                                    </a>
                                    <a href="{{ $job->getPublicProposalUrl() }}"
                                       class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                                       title="View Public Proposal">
                                        Public Link
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-8 text-center text-gray-500">
                                No jobs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($jobs->hasPages())
            <div class="px-4 py-3 border-t bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Showing {{ $jobs->firstItem() }} to {{ $jobs->lastItem() }} of {{ $jobs->total() }} results
                    </div>
                    <div class="flex gap-1">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

</body>

</html>

