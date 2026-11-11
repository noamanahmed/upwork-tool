<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_job_proposals', function (Blueprint $table) {
            $table->unsignedInteger('attempts')->default(0)->after('status');
            $table->text('last_error')->nullable()->after('attempts');
            $table->timestamp('queued_at')->nullable()->after('last_error');

            $table->index(['status', 'updated_at'], 'ai_job_proposals_status_updated_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('ai_job_proposals', function (Blueprint $table) {
            $table->dropIndex('ai_job_proposals_status_updated_at_index');
            $table->dropColumn(['attempts', 'last_error', 'queued_at']);
        });
    }
};
