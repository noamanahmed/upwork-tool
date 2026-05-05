<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JobController extends Controller
{
    /**
     * Display a paginated list of all jobs.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $jobs = Job::with('aiProposals')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('jobs.index', compact('jobs'));
    }
}
