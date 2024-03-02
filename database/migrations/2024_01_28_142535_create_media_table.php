<?php

use App\Enums\MediaTypeEnum;
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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mediable_id')->index();
            $table->string('mediable_type')->index();
            $table->string('key')->index();
            $table->integer('type')->default(MediaTypeEnum::UNKNOWN)->index();
            $table->mediumInteger('size')->default(0);
            $table->string('disk')->default(config('filesystems.default'));
            $table->string('original_name');
            $table->string('path');
            $table->datetimes();
            $table->index(['mediable_id','mediable_type','type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
