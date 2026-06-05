<?php

namespace App\Http\Controllers;

use App\Services\PublicProposalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PublicProposalController extends Controller
{
    /**
     * Show public proposal (no auth required).
     * Token is encrypted and expires in 24 hours.
     */
    public function show(string $token, PublicProposalService $publicProposalService)
    {
        $result = $publicProposalService->validateToken($token);

        if (!$result) {
            abort(404, 'Proposal link not found or has expired.');
        }

        $job = $result['job'];
        $proposal = $job->aiProposals()->latest()->first();        

        return view('public-proposal', compact('job', 'proposal'));
    }
}
