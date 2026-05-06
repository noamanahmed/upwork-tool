<?php

namespace App\Services;

use App\Models\Job;
use App\Models\AiJobProposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PublicProposalService
{
    /**
     * Generate a temporary signed URL for public proposal access.
     * Valid for 24 hours.
     *
     * @param Job $job
     * @param AiJobProposal|null $proposal
     * @return string
     */
    public function generateUrl(Job $job, ?AiJobProposal $proposal = null): string
    {
        if (!$proposal) {
            $provider = config('services.ai.provider');
            $proposal = $job->aiProposals()
                ->where('provider', $provider)
                ->orderByDesc('created_at')
                ->first();
        }

        if (!$proposal) {
            return '';
        }

        // Create a payload with job ID, proposal ID, and expiry timestamp
        $payload = [
            'job_id' => $job->id,
            'proposal_id' => $proposal->id,
            'expires_at' => Carbon::now()->addHours(24)->timestamp,
        ];

        // Encrypt and sign the payload
        $encrypted = Crypt::encryptString(json_encode($payload));

        return url("/public/proposal/{$encrypted}");
    }

    /**
     * Validate and decode the public access token.
     *
     * @param string $token
     * @return array|null Returns ['job' => Job, 'proposal' => AiJobProposal] or null if invalid/expired
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);

            if (!$payload || !isset($payload['job_id'], $payload['proposal_id'], $payload['expires_at'])) {
                return null;
            }

            // Check expiry
            if (Carbon::now()->timestamp > $payload['expires_at']) {
                return null;
            }

            $job = Job::find($payload['job_id']);
            $proposal = AiJobProposal::find($payload['proposal_id']);

            if (!$job || !$proposal || $proposal->job_id !== $job->id) {
                return null;
            }

            return [
                'job' => $job,
                'proposal' => $proposal,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
