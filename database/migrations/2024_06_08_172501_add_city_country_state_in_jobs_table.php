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
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('city')->after('is_payment_verified')->default('N/A');
            $table->string('state')->after('is_payment_verified')->default('N/A');
            $table->string('country')->after('is_payment_verified')->default('N/A');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['city','state','country']);
        });
    }
};
