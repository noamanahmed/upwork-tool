<?php

namespace App\Models;

use Illuminate\Support\Arr;

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
        $budget = $this->budget_minimum.$this->getCurrencySymbol().' - ' . $this->budget_maximum.$this->getCurrencySymbol();
        if($this->budget_minimum === $this->budget_maximum)
        {
            $budget = $this->budget_minimum.$this->getCurrencySymbol();
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
        $text .= ' *Job Title* '.$job->title;
        $text .= "\n";
        $text .= ' *Job Description* '.$job->description;
        $text .= "\n";
        $text .= '*Client Name* : '.$clientName;
        $text .= "\n";
        $text .= '*Client Location* : '.$this->location;
        $text .= "\n";
        $text .= '*Job Type* : '.$jobType;
        $text .= "\n";
        $text .= '*Budget* : '.$budget;
        $text .= "\n";
        $text .= '*Job Type* : '.$jobType;
        $text .= "\n";
        $text .= '*Project TotalApplicants* : '.$projectTotalApplicants;
        $text .= "\n";
        $text .= "\n";
        $text .= "\n";
        $text .= '*Client Details* : ';
        $text .= "\n";
        $text .= '*Total Hires* : '.$clientTotalHires;
        $text .= "\n";
        $text .= '*Total Spend* : '.$clientTotalSpend;
        $text .= "\n";
        $text .= '*Total Spend Currency* : '.$clientTotalSpendCurrency;
        $text .= "\n";
        $text .= '*Total Reviews* : '.$clientTotalReviews;
        $text .= "\n";
        $text .= '*Total Feedback* : '.$clientTotalFeedback;
        $text .= "\n";
        $text .= '*Total Posted Jobs* : '.$clientTotalPostedJobs;
        $text .= "\n";
        $text .= '*Job Link*  :' .'https://www.upwork.com/jobs/'.$job->ciphertext;
        return $text;
    }

    function getAdditonalData()
    {
        if(empty($this->json)) return;
        return Arr::dot(json_decode($this->json,true));
    }

    function getCurrencySymbol()
    {
        return '$';
    }
    public function searches()
    {
        return $this->belongsToMany(JobSearch::class,'job_searches_jobs_pivot')->withPivot(['is_slack_webhook_sent']);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class,'job_categories');
    }
    public function skills()
    {
        return $this->belongsToMany(Skill::class,'job_skills');
    }
    public function latestActivity()
    {
        return $this->hasOne(JobActivity::class)->latest();
    }
    public function activities()
    {
        return $this->hasMany(JobActivity::class);
    }
}
