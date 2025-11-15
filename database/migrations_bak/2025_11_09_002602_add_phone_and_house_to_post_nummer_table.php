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
        Schema::table('post_nummer', function (Blueprint $table) {
            if (! Schema::hasColumn('post_nummer', 'phone')) {
                $table->integer('phone')->nullable()->after('count');
            }
            if (! Schema::hasColumn('post_nummer', 'house')) {
                $table->integer('house')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_nummer', function (Blueprint $table) {
            if (Schema::hasColumn('post_nummer', 'house')) {
                $table->dropColumn('house');
            }
            if (Schema::hasColumn('post_nummer', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
