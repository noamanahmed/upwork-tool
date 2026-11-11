<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->removeDuplicatePairs();

        Schema::table('job_searches_jobs_pivot', function (Blueprint $table) {
            $table->unique(
                ['job_search_id', 'job_id'],
                'job_searches_jobs_pivot_pair_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('job_searches_jobs_pivot', function (Blueprint $table) {
            $table->dropUnique('job_searches_jobs_pivot_pair_unique');
        });
    }

    /**
     * Keep a single row per (job_search_id, job_id), preferring rows that were
     * already notified so no Slack message is re-sent after the index is added.
     */
    protected function removeDuplicatePairs(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                DELETE duplicate
                FROM job_searches_jobs_pivot AS duplicate
                INNER JOIN job_searches_jobs_pivot AS keeper
                    ON duplicate.job_search_id = keeper.job_search_id
                   AND duplicate.job_id = keeper.job_id
                   AND (
                        keeper.is_slack_webhook_sent > duplicate.is_slack_webhook_sent
                        OR (
                            keeper.is_slack_webhook_sent = duplicate.is_slack_webhook_sent
                            AND keeper.id < duplicate.id
                        )
                   )
            SQL);

            return;
        }

        $duplicates = DB::table('job_searches_jobs_pivot')
            ->select('job_search_id', 'job_id')
            ->groupBy('job_search_id', 'job_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $pair) {
            $ids = DB::table('job_searches_jobs_pivot')
                ->where('job_search_id', $pair->job_search_id)
                ->where('job_id', $pair->job_id)
                ->orderByDesc('is_slack_webhook_sent')
                ->orderBy('id')
                ->pluck('id');

            DB::table('job_searches_jobs_pivot')
                ->whereIn('id', $ids->slice(1)->all())
                ->delete();
        }
    }
};
