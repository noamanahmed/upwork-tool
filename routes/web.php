<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicProposalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect to Filament Panel
Route::redirect('/', '/admin/jobs');

// Authentication & Auth0 Routes
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/login/auth0', [AuthController::class, 'redirectToAuth0'])->name('auth0.redirect');
Route::get('/login/auth0/callback', [AuthController::class, 'handleAuth0Callback'])->name('auth0.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Proposal (temporary signed link, 24h, unauthenticated access)
Route::get('/public/proposal/{token}', [PublicProposalController::class, 'show'])->name('public.proposal');

// Legacy Jobs Routes -> Redirect to Filament Resources
Route::get('/jobs', function () {
    return redirect('/admin/jobs');
})->name('jobs.index');

Route::get('/job/{jobId}/proposal', function ($jobId) {
    return redirect("/admin/jobs/{$jobId}/proposal");
})->name('job.proposal');
