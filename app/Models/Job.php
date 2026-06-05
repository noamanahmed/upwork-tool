<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class Job extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];


    function getSlackNotificationMessageAttribute()
    {
        $data = $this->getAdditonalData();
        $clientName = $data['node.job.ownership.team.name'] ?? 'N/A';
        $budget = $this->budget_minimum . $this->getCurrencySymbol() . ' - ' . $this->budget_maximum . $this->getCurrencySymbol();
        if ($this->budget_minimum === $this->budget_maximum) {
            $budget = $this->budget_minimum . $this->getCurrencySymbol();
        }
        $jobType = $this->is_hourly ? 'HOURLY' : 'FIXED RATE';
        $projectTotalApplicants = $data['node.totalApplicants'] ?? 'N/A';
        $clientTotalHires = $data['node.client.totalHires'] ?? 'N/A';
        $clientTotalSpend = $data['node.client.totalSpent.rawValue'] ?? 'N/A';
        $clientTotalSpendCurrency = $data['node.client.totalSpent.currency'] ?? 'N/A';
        $clientTotalReviews = $data['node.client.totalReviews'] ?? 'N/A';
        $clientTotalFeedback = $data['node.client.totalFeedback'] ?? 'N/A';
        $clientTotalPostedJobs = $data['node.client.totalPostedJobs'] ?? 'N/A';
        $job = $this;
        $text = '';
        $text .= '<!channel>';
        $text .= "\n";
        $text .= ' *Job Title* ' . $job->title;
        $text .= "\n";
        $text .= ' *Job Description* ' . $job->description;
        $text .= "\n";
        $text .= '*Client Name* : ' . $clientName;
        $text .= "\n";
        $text .= '*Client Location* : ' . $this->location;
        $text .= "\n";
        $text .= '*Job Type* : ' . $jobType;
        $text .= "\n";
        $text .= '*Budget* : ' . $budget;
        $text .= "\n";
        $text .= '*Job Type* : ' . $jobType;
        $text .= "\n";
        $text .= '*Project TotalApplicants* : ' . $projectTotalApplicants;
        $text .= "\n";
        $text .= "\n";
        $text .= "\n";
        $text .= '*Client Details* : ';
        $text .= "\n";
        $text .= '*Total Hires* : ' . $clientTotalHires;
        $text .= "\n";
        $text .= '*Total Spend* : ' . $clientTotalSpend;
        $text .= "\n";
        $text .= '*Total Spend Currency* : ' . $clientTotalSpendCurrency;
        $text .= "\n";
        $text .= '*Total Reviews* : ' . $clientTotalReviews;
        $text .= "\n";
        $text .= '*Total Feedback* : ' . $clientTotalFeedback;
        $text .= "\n";
        $text .= '*Total Posted Jobs* : ' . $clientTotalPostedJobs;
        $text .= "\n";
        $text .= '*Job Link*  :' . 'https://www.upwork.com/jobs/' . $job->ciphertext;
        $text .= "\n";
        $text .= '*Proposal Link (Login Required)*  :' . route('job.proposal', ['jobId' => $job->id]);
        $text .= "\n";

        // Public proposal link (24h temporary, no proposal ID in URL)
        $publicUrl = $job->getPublicProposalUrl();
        if ($publicUrl) {
            $text .= '*Public Proposal Link (24h)* : <' . $publicUrl . '>';
            $text .= "\n";
        }

        return $text;
    }

    function getAdditonalData()
    {
        if (empty($this->json))
            return;
        return Arr::dot(json_decode($this->json, true));
    }

    function getCurrencySymbol()
    {
        return '$';
    }
    public function searches()
    {
        return $this->belongsToMany(JobSearch::class, 'job_searches_jobs_pivot')->withPivot(['is_slack_webhook_sent']);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_categories');
    }
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_skills');
    }
    public function latestActivity()
    {
        return $this->hasOne(JobActivity::class)->latest();
    }
    public function activities()
    {
        return $this->hasMany(JobActivity::class);
    }

    public function aiProposals()
    {
        return $this->hasMany(AiJobProposal::class);
    }

    public function agentConversation()
    {
        return $this->hasOne(AiAgentConversation::class);
    }

    /**
     * Get a temporary public proposal URL (valid 24 hours).
     * URL contains only job identifier - proposal is fetched server-side.
     */
    public function getPublicProposalUrl(): string
    {
        $expiresAt = now()->addHours(24)->timestamp;

        $payload = [
            'job_id' => $this->id,
            'expires_at' => $expiresAt,
        ];

        $payload['signature'] = hash_hmac(
            'sha256',
            $payload['job_id'] . $payload['expires_at'],
            config('app.key')
        );

        $encrypted = Crypt::encryptString(json_encode($payload));

        /**
         * IMPORTANT:
         * Do NOT use urlencode() here — it breaks Slack link parsing
         * and is unnecessary because route() / url() already handles encoding safely.
         */
        return url("/public/proposal/{$encrypted}");
    }
}
