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
            $table->unsignedInteger('last_processed_page')->nullable()->after('progress');
            $table->unsignedInteger('processed_count')->nullable()->after('last_processed_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_nummer', function (Blueprint $table) {
            $table->dropColumn(['last_processed_page', 'processed_count']);
        });
    }
};
