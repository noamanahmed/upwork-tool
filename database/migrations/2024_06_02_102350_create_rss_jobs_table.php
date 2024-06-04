<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rss_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_job_search_id')->constrained('rss_job_searches')->onDelete('cascade');
            $table->mediumText('ciphertext');
            $table->boolean('is_slack_webhook_sent')->default(0)->index();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_jobs');
    }
};
