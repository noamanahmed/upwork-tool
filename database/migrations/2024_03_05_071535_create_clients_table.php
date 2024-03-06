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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upwork_id')->index()->unique();
            $table->string('name');
            $table->string('type');
            $table->mediumInteger('total_hires');
            $table->mediumInteger('total_posted_jobs');
            $table->mediumInteger('total_reviews');
            $table->decimal('total_feedback');
            $table->mediumInteger('total_spent');
            $table->string('total_spent_currency');
            $table->boolean('is_prefixed_total_spent_currency')->default(0);
            $table->string('verification_status');


            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
