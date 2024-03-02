<?php

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('email_verification_sent_at')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->string('password');
            $table->string('phone')->nullable()->default(null);
            $table->mediumText('address')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('country')->nullable()->default(null);
            $table->integer('status')->default(UserStatusEnum::EMAIL_UNVERIFIED);
            $table->integer('type')->default(UserTypeEnum::READ_ONLY);
            $table->softDeletesDatetime();
            $table->rememberToken();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
