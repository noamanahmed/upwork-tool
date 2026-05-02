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


Route::get('/job/{jobId}/proposal', function ($jobId) {
    $job = \App\Models\Job::findOrFail($jobId);
    return view('job-proposal', compact('job'));
})->name('job.proposal');
