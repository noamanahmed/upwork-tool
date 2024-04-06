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
