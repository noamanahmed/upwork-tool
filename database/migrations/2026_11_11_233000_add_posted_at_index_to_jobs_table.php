<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->index('posted_at', 'jobs_posted_at_index');
        });

        Schema::table('job_searches_jobs_pivot', function (Blueprint $table) {
            $table->index(['job_search_id', 'job_id'], 'job_searches_jobs_pivot_search_job_index');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_posted_at_index');
        });

        Schema::table('job_searches_jobs_pivot', function (Blueprint $table) {
            $table->dropIndex('job_searches_jobs_pivot_search_job_index');
        });
    }
};
