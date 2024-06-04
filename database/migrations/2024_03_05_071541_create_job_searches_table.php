<?php

use App\Enums\JobSearchStatusEnum;
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
        Schema::create('job_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('q')->nullable();
            $table->string('sort')->nullable();
            $table->string('category')->nullable();
            $table->string('experience_level')->nullable();
            $table->boolean('is_job_type_hourly')->nullable();
            $table->string('hourly_rate_minimum')->nullable();
            $table->string('hourly_rate_maximum')->nullable();
            $table->boolean('is_job_type_fixed')->nullable();
            $table->double('fixed_rate_minimum')->nullable();
            $table->double('fixed_rate_maximum')->nullable();
            $table->double('proposals_minimum')->nullable();
            $table->double('proposals_maximum')->nullable();
            $table->boolean('is_previous_client')->nullable();
            $table->boolean('is_payment_verified')->nullable();
            $table->double('client_previous_hired_minimum')->nullable();
            $table->double('client_previous_hired_maximum')->nullable();
            $table->string('location')->nullable();
            $table->string('timezone')->nullable();
            $table->string('duration_type')->nullable();
            $table->string('workload_type')->nullable();
            $table->double('connect_required_minimum')->nullable();
            $table->double('connect_required_maximum')->nullable();
            $table->boolean('is_contract_to_hire')->nullable();
            $table->string('slack_webhook_url')->nullable();
            $table->integer('status')->default(JobSearchStatusEnum::ACTIVE);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_searches');
    }
};
