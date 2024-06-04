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
        Schema::table('job_searches', function (Blueprint $table) {
            $table->double('feedback_minimum')->after('is_payment_verified')->default(0.001);
            $table->double('feedback_maximum')->after('is_payment_verified')->default(5.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_searches', function (Blueprint $table) {
            $table->dropColumn(['feedback_minimum','feedback_maximum']);
        });
    }
};
