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
        $text .= "\n\n";
        $text .= ' *Job Title* '.$job->title;
        $text .= "\n\n";
        $text .= ' *Job Description* '.$job->description;
        $text .= "\n\n";
        $text .= '*Client Id* : '.$clientId;
        $text .= "\t\t";
        $text .= '*Client Name* : '.$clientName;
        $text .= "\t\t";
        $text .= '*Client Budget* : '.$clientBudget;
        $text .= "\n\n";
        $text .= '*Client BudgetCurrency* : '.$clientBudgetCurrency;
        $text .= "\t\t";
        $text .= '*Client WeeklyBudget* : '.$clientWeeklyBudget;
        $text .= "\t\t";
        $text .= '*Client WeeklyBudgetCurrency* : '.$clientWeeklyBudgetCurrency;
        $text .= "\n\n";
        $text .= '*Client Type* : '.$clientType;
        $text .= "\t\t";
        $text .= '*Average Rate Bid* : '.$averageRateBid;
        $text .= "\t\t";
        $text .= '*Average Rate Currency* : '.$averageRateCurrency;
        $text .= "\n\n";
        $text .= '*Maximum Rate Bid* : '.$maximumRateBid;
        $text .= "\t\t";
        $text .= '*Maximum Rate Currency* : '.$maximumRateCurrency;
        $text .= "\t\t";
        $text .= '*Minimum Rate Bid* : '.$minimumRateBid;
        $text .= "\n\n";
        $text .= '*Minimum Rate Currency* : '.$minimumRateCurrency;
        $text .= "\t\t";
        $text .= '*Engagement* : '.$engagement;
        $text .= "\t\t";
        $text .= '*Project TotalApplicants* : '.$projectTotalApplicants;
        $text .= "\t\t";
        $text .= '*Client Total Hires* : '.$clientTotalHires;
        $text .= "\n\n";
        $text .= '*Client Total Spend* : '.$clientTotalSpend;
        $text .= "\t\t";
        $text .= '*Client Total Spend Currency* : '.$clientTotalSpendCurrency;
        $text .= "\t\t";
        $text .= '*Client Total Reviews* : '.$clientTotalReviews;
        $text .= "\n\n";
        $text .= '*Client Total Feedback* : '.$clientTotalFeedback;
        $text .= "\t\t";
        $text .= '*Client Total Posted Jobs* : '.$clientTotalPostedJobs;
        $text .= "\n\n";
        $text .= '*Job Link*  :' .'https://www.upwork.com/jobs/'.$job->ciphertext;
        $text .= "\n\n";
        $text .= '*Job Link(Opens in App)*  :' .'upwork://www.upwork.com/jobs/'.$job->ciphertext;

        return $text;
    }

    function getAdditonalData()
    {
        if(empty($this->json)) return;
        return Arr::dot(json_decode($this->json,true));
    }
}
