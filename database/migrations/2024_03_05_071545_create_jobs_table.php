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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upwork_id')->index()->unique();
            // $table->foreignId('client_id')->constrained('clients')->nullable()->onDelete('cascade');
            $table->string('ciphertext')->nullable();
            $table->string('title');
            $table->mediumText('description');
            $table->boolean('is_slack_webhook_sent')->default(0)->index();
            $table->mediumInteger('client_total_hires')->nullable();
            $table->mediumInteger('client_total_posted_jobs')->nullable();
            $table->mediumInteger('client_total_reviews')->nullable();
            $table->decimal('client_total_feedback')->nullable();
            $table->mediumInteger('client_total_spent')->nullable();
            $table->string('client_total_spent_currency')->nullable();
            $table->string('location')->nullable();
            $table->double('budget_minimum')->default(0);
            $table->double('budget_maximum')->default(0);
            $table->boolean('is_hourly')->default(0);
            $table->boolean('is_payment_verified')->default(0);
            $table->json('json');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
