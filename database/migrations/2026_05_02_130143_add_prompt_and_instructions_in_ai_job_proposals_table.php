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
        Schema::table('ai_job_proposals', function (Blueprint $table) {
            $table->mediumText('prompt')->nullable()->after('proposal');
            $table->mediumText('instructions')->nullable()->after('prompt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_job_proposals', function (Blueprint $table) {
            $table->dropColumn('prompt');
            $table->dropColumn('instructions');
        });
    }
};
