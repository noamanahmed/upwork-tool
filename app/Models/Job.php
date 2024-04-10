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

        $clientId = $data['node.job.ownership.team.id'] ?? 'N/A';
        $clientName = $data['node.job.ownership.team.name'] ?? 'N/A';
        $clientType = $data['node.job.ownership.team.type'] ?? 'N/A';
        $clientBudget = $data['node.amount.rawValue'] ?? 'N/A';
        $clientBudgetCurrency = $data['node.amount.currency'] ?? 'N/A';
        $clientWeeklyBudget = $data['node.weeklyBudget.rawValue'] ?? 'N/A';
        $clientWeeklyBudgetCurrency = $data['node.weeklyBudget.currency'] ?? 'N/A';
        $clientHourlyBudgetMinimum = $data['node.hourlyBudgetMin.rawValue'] ?? 'N/A';
        $clientHourlyBudgetMinimumCurrency = $data['node.hourlyBudgetMax.currency'] ?? 'N/A';
        $clientHourlyBudgetMaximum = $data['node.hourlyBudgetMax.rawValue'] ?? 'N/A';
        $clientHourlyBudgetMaximumCurrency = $data['node.hourlyBudgetMin.currency'] ?? 'N/A';
        $projectTotalApplicants = $data['node.totalApplicants'] ?? 'N/A';
        $averageRateBid = $data['node.job.activityStat.applicationsBidStats.avgRateBid.rawValue'] ?? 'N/A';
        $averageRateCurrency = $data['node.job.activityStat.applicationsBidStats.avgRateBid.currency'] ?? 'N/A';
        $maximumRateBid = $data['node.job.activityStat.applicationsBidStats.maxRateBid.rawValue'] ?? 'N/A';
        $maximumRateCurrency = $data['node.job.activityStat.applicationsBidStats.maxRateBid.currency'] ?? 'N/A';
        $minimumRateBid = $data['node.job.activityStat.applicationsBidStats.minRateBid.rawValue'] ?? 'N/A';
        $minimumRateCurrency = $data['node.job.activityStat.applicationsBidStats.minRateBid.currency'] ?? 'N/A';
        $engagement = $data['node.engagement'] ?? 'N/A';
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
        $text .= '*Client Id* : '.$clientId;
        $text .= "\n";
        $text .= '*Client Name* : '.$clientName;
        $text .= "\n";
        $text .= '*Client Budget* : '.$clientBudget;
        $text .= "\n";
        $text .= '*Client BudgetCurrency* : '.$clientBudgetCurrency;
        $text .= "\n";
        $text .= '*Client WeeklyBudget* : '.$clientWeeklyBudget;
        $text .= "\n";
        $text .= '*Client WeeklyBudgetCurrency* : '.$clientWeeklyBudgetCurrency;
        $text .= "\n";
        $text .= '*Client Hourly Budget Minimum* :'. $clientHourlyBudgetMinimum;
        $text .= "\n";
        $text .= '*Client Hourly Budget Minimum Currency* :'. $clientHourlyBudgetMinimumCurrency;
        $text .= "\n";
        $text .= '*Client Hourly Budget Maximum* :'. $clientHourlyBudgetMaximum;
        $text .= "\n";
        $text .= '*Client Hourly Budget Maximum Currency* :'. $clientHourlyBudgetMaximumCurrency;
        $text .= "\n";
        $text .= '*Client Type* : '.$clientType;
        $text .= "\n";
        $text .= '*Average Rate Bid* : '.$averageRateBid;
        $text .= "\n";
        $text .= '*Average Rate Currency* : '.$averageRateCurrency;
        $text .= "\n";
        $text .= '*Maximum Rate Bid* : '.$maximumRateBid;
        $text .= "\n";
        $text .= '*Maximum Rate Currency* : '.$maximumRateCurrency;
        $text .= "\n";
        $text .= '*Minimum Rate Bid* : '.$minimumRateBid;
        $text .= "\n";
        $text .= '*Minimum Rate Currency* : '.$minimumRateCurrency;
        $text .= "\n";
        $text .= '*Engagement* : '.$engagement;
        $text .= "\n";
        $text .= '*Project TotalApplicants* : '.$projectTotalApplicants;
        $text .= "\n";
        $text .= '*Client Total Hires* : '.$clientTotalHires;
        $text .= "\n";
        $text .= '*Client Total Spend* : '.$clientTotalSpend;
        $text .= "\n";
        $text .= '*Client Total Spend Currency* : '.$clientTotalSpendCurrency;
        $text .= "\n";
        $text .= '*Client Total Reviews* : '.$clientTotalReviews;
        $text .= "\n";
        $text .= '*Client Total Feedback* : '.$clientTotalFeedback;
        $text .= "\n";
        $text .= '*Client Total Posted Jobs* : '.$clientTotalPostedJobs;
        $text .= "\n";
        $text .= '*Job Link*  :' .'https://www.upwork.com/jobs/'.$job->ciphertext;
        $text .= "\n";
        $text .= '*Job Link(Opens in App)*  :' .'upwork://www.upwork.com/jobs/'.$job->ciphertext;

        return $text;
    }

    function getAdditonalData()
    {
        if(empty($this->json)) return;
        return Arr::dot(json_decode($this->json,true));
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
}
