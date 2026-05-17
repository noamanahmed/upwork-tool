<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you may register web routes for your application.
| These routes are loaded by the RouteServiceProvider within the "web" group.
| All routes are assigned the "web" middleware group.
|
*/

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login/auth0', [AuthController::class, 'redirectToAuth0'])->name('auth0.redirect');
Route::get('/login/auth0/callback', [AuthController::class, 'handleAuth0Callback'])->name('auth0.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Proposal (temporary signed link, 24h)
Route::get('/public/proposal/{token}', [App\Http\Controllers\PublicProposalController::class, 'show'])->name('public.proposal');

// Jobs Routes (protected: authenticated + verified)
Route::middleware(['auth', 'verified.user'])->group(function () {
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/job/{jobId}/proposal', function ($jobId) {
        $job = \App\Models\Job::findOrFail($jobId);
        $enabledProviders = config('services.ai.enabled_providers', []);
        $availableProviders = config('services.ai.available_providers', []);

        $proposals = \App\Models\AiJobProposal::where('job_id', $jobId)
            ->whereIn('provider', $availableProviders)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('provider');

        // Visible tabs are those that are enabled OR have an existing proposal
        $visibleTabs = collect($availableProviders)->filter(function ($p) use ($enabledProviders, $proposals) {
            return in_array($p, $enabledProviders) || $proposals->has($p);
        })->values();

        // Keep backward-compat $proposal = first available (for timeline section)
        $proposal = $proposals->first();

        return view('job-proposal', compact('job', 'proposal', 'proposals', 'enabledProviders', 'visibleTabs'));
    })->name('job.proposal');
});

