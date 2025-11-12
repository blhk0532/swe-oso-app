<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First update existing nulls to 0
        DB::table('post_nummer')->whereNull('phone')->update(['phone' => 0]);
        DB::table('post_nummer')->whereNull('house')->update(['house' => 0]);

        // Then change column defaults (SQLite will recreate table automatically)
        Schema::table('post_nummer', function ($table) {
            if (Schema::hasColumn('post_nummer', 'phone')) {
                $table->integer('phone')->default(0)->nullable(false)->change();
            }
            if (Schema::hasColumn('post_nummer', 'house')) {
                $table->integer('house')->default(0)->nullable(false)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_nummer', function ($table) {
            if (Schema::hasColumn('post_nummer', 'phone')) {
                $table->integer('phone')->nullable()->default(null)->change();
            }
            if (Schema::hasColumn('post_nummer', 'house')) {
                $table->integer('house')->nullable()->default(null)->change();
            }
        });
    }
};
