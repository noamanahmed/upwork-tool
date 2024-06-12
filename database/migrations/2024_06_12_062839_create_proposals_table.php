<?php

use App\Enums\ProposalStatusEnum;
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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proposal_id')->index()->unique();
            $table->foreignId('job_id')->nullable()->constrained('jobs')->onDelete('SET NULL');
            $table->unsignedBigInteger('upwork_job_id')->index()->unique();
            $table->mediumText('cover_letter');
            $table->string('duration')->default('N/A');
            $table->double('bid')->default(0);
            $table->string('currency')->default('N/A');
            $table->integer('status')->default(ProposalStatusEnum::ACCEPTED);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
