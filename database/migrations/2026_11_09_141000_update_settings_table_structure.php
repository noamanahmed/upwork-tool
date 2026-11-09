<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasKey = Schema::hasColumn('settings', 'key');

        Schema::table('settings', function (Blueprint $table) use ($hasKey) {
            if (!$hasKey) {
                $table->string('key')->nullable()->after('id');
            }
            if (!Schema::hasColumn('settings', 'default_value')) {
                $table->longText('default_value')->nullable()->after('value');
            }
            if (!Schema::hasColumn('settings', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(false)->after('default_value');
            }
            if (!Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('is_encrypted');
            }
        });

        // Migrate any existing 'name' values to 'key' if key was just added or is null
        if (Schema::hasColumn('settings', 'name') && Schema::hasColumn('settings', 'key')) {
            DB::table('settings')->whereNull('key')->update([
                'key' => DB::raw('`name`'),
            ]);
        }

        // Adjust column types if key was newly added
        if (!$hasKey) {
            Schema::table('settings', function (Blueprint $table) {
                $table->longText('value')->nullable()->change();
                if (Schema::hasColumn('settings', 'name')) {
                    $table->string('name')->nullable()->change();
                }
                $table->string('key')->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'key')) {
                $table->dropUnique(['key']);
                $table->dropColumn('key');
            }
            if (Schema::hasColumn('settings', 'default_value')) {
                $table->dropColumn('default_value');
            }
            if (Schema::hasColumn('settings', 'is_encrypted')) {
                $table->dropColumn('is_encrypted');
            }
            if (Schema::hasColumn('settings', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
