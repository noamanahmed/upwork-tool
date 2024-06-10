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
        Schema::create('job_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->string('schedule')->default('DEFAULT');
            $table->integer('total_applicants')->default(0);
            $table->double('average_rate_bid')->default(0);
            $table->double('minimum_rate_bid')->default(0);
            $table->double('maximum_rate_bid')->default(0);
            $table->double('interview_rate_bid')->default(0);
            $table->double('invites_sent')->default(0);
            $table->double('total_invited_to_interview')->default(0);
            $table->double('total_hired')->default(0);
            $table->double('total_unanswered_invites')->default(0);
            $table->double('total_offered')->default(0);
            $table->double('total_recommended')->default(0);
            $table->string('last_client_activity')->default('N/A');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_activities');
    }
};
