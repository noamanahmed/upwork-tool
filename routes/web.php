<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/jobs', [App\Http\Controllers\JobController::class, 'index'])->name('jobs.index');

Route::get('/job/{jobId}/proposal', function ($jobId) {
    $job = \App\Models\Job::findOrFail($jobId);
    $provider = config('services.ai.provider');
    $proposal = $job->aiProposals()
        ->where('provider', $provider)
        ->orderByDesc('created_at')
        ->first();
    return view('job-proposal', compact('job', 'proposal'));
})->name('job.proposal');
