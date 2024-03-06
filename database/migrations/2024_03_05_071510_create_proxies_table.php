<?php

use App\Enums\ProxyStatusEnum;
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
        Schema::create('proxies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('http')->index();
            $table->string('host');
            $table->integer('port')->default(0);
            $table->string('username')->default('');
            $table->string('password')->default('');
            $table->integer('tier')->default(1)->index();
            $table->unsignedBigInteger('usage')->default(0)->index();
            $table->unsignedBigInteger('success')->default(0);
            $table->unsignedBigInteger('blocked')->default(0);
            $table->dateTime('last_used')->nullable()->index();
            $table->integer('status')->default(ProxyStatusEnum::ACTIVE);
            $table->unique(['type', 'host','port','username','password']);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxies');
    }
};
