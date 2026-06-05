<?php

namespace App\Services;

use App\Models\Job;
use App\Models\AiJobProposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class PublicProposalService
{
    /**
     * Generate a temporary signed URL for public proposal access.
     * Valid for 24 hours. Contains only job ID (proposal fetched server-side).
     *
     * @param Job $job
     * @return string
     */
    public function generateUrl(Job $job): string
    {
        $payload = [
            'job_id' => $job->id,
            'expires_at' => Carbon::now()->addHours(24)->timestamp,
            // HMAC signature to prevent tampering
            'signature' => hash_hmac('sha256', $job->id . Carbon::now()->addHours(24)->timestamp, config('app.key')),
        ];

        $encrypted = Crypt::encryptString(json_encode($payload));
        return url("/public/proposal/{$encrypted}");
    }

    /**
     * Validate and decode the public access token.
     * Fetches the latest proposal for the job automatically.
     *
     * @param string $token
     * @return array|null Returns ['job' => Job, 'proposal' => AiJobProposal] or null if invalid/expired
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);
            
            if (!$payload || !isset($payload['job_id'], $payload['expires_at'], $payload['signature'])) {
                return null;
            }

            // Check expiry
            if (Carbon::now()->timestamp > $payload['expires_at']) {
                return null;
            }
            // Verify signature
            $expectedSignature = hash_hmac('sha256', $payload['job_id'] . $payload['expires_at'], config('app.key'));
            if (!hash_equals($expectedSignature, $payload['signature'])) {
                return null;
            }
            

            $job = Job::find($payload['job_id']);
            
            if (!$job) {
                return null;
            }

            return [
                'job' => $job             
            ];
        } catch (\Exception $e) {            
            return null;
        }
    }
}
