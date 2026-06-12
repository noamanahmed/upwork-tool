<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .search-tag { transition: all 0.15s ease; }
        .search-tag:hover { transform: translateY(-1px); }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

<div class="max-w-full mx-auto px-4 py-8">

    <div class="mb-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Jobs</h1>
        <div class="flex gap-2">
            <a href="{{ route('jobs.index', array_merge(request()->except('per_page'), ['per_page' => 15])) }}"
               class="px-3 py-1 text-sm border rounded {{ request()->get('per_page') == 15 ? 'bg-gray-200' : 'bg-white' }}">15</a>
            <a href="{{ route('jobs.index', array_merge(request()->except('per_page'), ['per_page' => 50])) }}"
               class="px-3 py-1 text-sm border rounded {{ request()->get('per_page') == 50 ? 'bg-gray-200' : 'bg-white' }}">50</a>
            <a href="{{ route('jobs.index', array_merge(request()->except('per_page'), ['per_page' => 100])) }}"
               class="px-3 py-1 text-sm border rounded {{ request()->get('per_page') == 100 ? 'bg-gray-200' : 'bg-white' }}">100</a>
        </div>
    </div>

    <!-- Category Filter Bar -->
    <div class="mb-4 bg-white rounded-xl shadow border p-4">
        <div class="text-sm text-gray-500 mb-2 font-medium">Filter by Search Category</div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('jobs.index', request()->except(['search_id', 'page'])) }}"
               class="search-tag px-3 py-1 text-xs rounded-full border
                      {{ is_null($selectedSearchId) ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                All
            </a>
            @foreach($allSearches as $search)
                @php
                    $isActive = (string) $selectedSearchId === (string) $search->id;
                @endphp
                <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['search_id' => $search->id])) }}"
                   class="search-tag px-3 py-1 text-xs rounded-full border
                          {{ $isActive ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400 hover:text-blue-600' }}">
                    {{ $search->name }}
                    <span class="ml-1 opacity-70">({{ $search->jobs_count }})</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-4 bg-white rounded-xl shadow border p-4">
        <form method="GET" action="{{ route('jobs.index') }}" class="flex gap-3 items-center">
            @foreach(['sort','dir','per_page','search_id'] as $preserve)
                @if(request()->get($preserve))
                    <input type="hidden" name="{{ $preserve }}" value="{{ request()->get($preserve) }}">
                @endif
            @endforeach
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       placeholder="Search by job title or description..."
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Search
            </button>
            <a href="{{ route('jobs.index') }}"
               class="px-4 py-2 text-sm border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                Reset
            </a>
        </form>
    </div>

    <!-- Filter Panel -->
    <div class="mb-4 bg-white rounded-xl shadow border overflow-hidden">
        <details class="group" {{ count(array_filter($filters, fn($v) => $v !== null && $v !== '')) > 0 ? 'open' : '' }}>
            <summary class="px-4 py-3 bg-gray-50 border-b cursor-pointer flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition select-none">
                <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                Filters
                @php
                    $activeFilterCount = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
                @endphp
                @if($activeFilterCount > 0)
                    <span class="ml-1 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ $activeFilterCount }} active</span>
                @endif
            </summary>
            <form method="GET" action="{{ route('jobs.index') }}" class="p-4">
                @foreach(['sort','dir','per_page','search_id','search'] as $preserve)
                    @if(request()->get($preserve))
                        <input type="hidden" name="{{ $preserve }}" value="{{ request()->get($preserve) }}">
                    @endif
                @endforeach

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">All</option>
                            <option value="none" {{ ($filters['status'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="generating" {{ ($filters['status'] ?? '') === 'generating' ? 'selected' : '' }}>Generating</option>
                            <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select name="type" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">All</option>
                            <option value="hourly" {{ ($filters['type'] ?? '') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="fixed" {{ ($filters['type'] ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Rate</option>
                        </select>
                    </div>

                    <!-- Spend Currency -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Spend Currency</label>
                        <select name="spend_currency" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">All</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency }}" {{ ($filters['spend_currency'] ?? '') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Ranges -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Budget -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Budget ($)</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="budget_min" value="{{ $filters['budget_min'] ?? '' }}" placeholder="Min"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="budget_max" value="{{ $filters['budget_max'] ?? '' }}" placeholder="Max"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Applicants -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Applicants</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="applicants_min" value="{{ $filters['applicants_min'] ?? '' }}" placeholder="Min"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="applicants_max" value="{{ $filters['applicants_max'] ?? '' }}" placeholder="Max"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Spend -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Spend</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="spend_min" value="{{ $filters['spend_min'] ?? '' }}" placeholder="Min"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="spend_max" value="{{ $filters['spend_max'] ?? '' }}" placeholder="Max"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Posted Jobs -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Posted Jobs</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="posted_jobs_min" value="{{ $filters['posted_jobs_min'] ?? '' }}" placeholder="Min"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="posted_jobs_max" value="{{ $filters['posted_jobs_max'] ?? '' }}" placeholder="Max"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Hires -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hires</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="hires_min" value="{{ $filters['hires_min'] ?? '' }}" placeholder="Min"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="hires_max" value="{{ $filters['hires_max'] ?? '' }}" placeholder="Max"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Reviews</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="reviews_min" value="{{ $filters['reviews_min'] ?? '' }}" placeholder="Min"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="reviews_max" value="{{ $filters['reviews_max'] ?? '' }}" placeholder="Max"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Feedback</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="feedback_min" value="{{ $filters['feedback_min'] ?? '' }}" placeholder="Min" step="0.1"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="number" name="feedback_max" value="{{ $filters['feedback_max'] ?? '' }}" placeholder="Max" step="0.1"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Posted At -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Posted At</label>
                        <div class="flex gap-2 items-center">
                            <input type="date" name="posted_from" value="{{ $filters['posted_from'] ?? '' }}"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-gray-400 text-sm">—</span>
                            <input type="date" name="posted_to" value="{{ $filters['posted_to'] ?? '' }}"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 border-t pt-4">
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('jobs.index', array_intersect_key(request()->query(), array_flip(['sort','dir','per_page']))) }}"
                       class="px-4 py-2 text-sm border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Clear Filters
                    </a>
                </div>
            </form>
        </details>
    </div>

    <div class="bg-white rounded-xl shadow border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                    <tr>
                        @php
                            $headers = [
                                ['label' => 'Job Title', 'sort' => 'title'],
                                ['label' => 'Categories', 'sort' => null],
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
                                $query = $header['sort'] ? array_merge(request()->query(), ['sort' => $header['sort'], 'dir' => $nextDir]) : '#';
                            @endphp
                            <th class="px-4 py-3">
                                @if($header['sort'])
                                    <a href="{{ route('jobs.index', $query) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition"
                                       title="Sort by {{ $header['label'] }}">
                                        <span>{{ $header['label'] }}</span>
                                        <span class="text-xs">{{ $icon }}</span>
                                    </a>
                                @else
                                    <span>{{ $header['label'] }}</span>
                                @endif
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
                                <div class="flex flex-wrap gap-1">
                                    @forelse($job->searches as $search)
                                        <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['search_id' => $search->id])) }}"
                                           class="search-tag px-2 py-0.5 text-xs rounded-full
                                                  {{ (string) $selectedSearchId === (string) $search->id ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-600 border border-gray-200 hover:border-blue-300 hover:text-blue-600' }}"
                                           title="Filter by {{ $search->name }}">
                                            {{ \Illuminate\Support\Str::limit($search->name, 20) }}
                                        </a>
                                    @empty
                                        <span class="text-xs text-gray-400">—</span>
                                    @endforelse
                                </div>
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
                                <span class="text-gray-500 text-xs" title="{{ $job->created_at->diffForHumans() }}">
                                    @php $adminTimezone = config('admin.personal.timezone','UTC'); @endphp
                                    {{ $job->created_at->timezone($adminTimezone)->format('Y-m-d H:i:s') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="https://www.upwork.com/jobs/~{{ ltrim($job->ciphertext, '~') }}"
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
                            <td colspan="15" class="px-4 py-8 text-center text-gray-500">
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

