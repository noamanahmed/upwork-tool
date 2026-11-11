<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_searches_jobs_pivot', function (Blueprint $table) {
            $table->timestamp('slack_sent_at')->nullable()->after('is_slack_webhook_sent');
            $table->unsignedTinyInteger('slack_attempts')->default(0)->after('slack_sent_at');
            $table->text('slack_last_error')->nullable()->after('slack_attempts');

            $table->index(
                ['is_slack_webhook_sent', 'created_at'],
                'job_searches_jobs_pivot_pending_slack_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('job_searches_jobs_pivot', function (Blueprint $table) {
            $table->dropIndex('job_searches_jobs_pivot_pending_slack_index');
            $table->dropColumn(['slack_sent_at', 'slack_attempts', 'slack_last_error']);
        });
    }
};
