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
        // Add total columns to merinfo_data table
        Schema::table('merinfo_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('merinfo_data', 'merinfo_personer_total')) {
                $table->integer('merinfo_personer_total')->nullable()->after('is_hus');
            }
            if (! Schema::hasColumn('merinfo_data', 'merinfo_foretag_total')) {
                $table->integer('merinfo_foretag_total')->nullable()->after('merinfo_personer_total');
            }
        });

        // Add total columns to post_nummer table
        Schema::table('post_nummer', function (Blueprint $table): void {
            if (! Schema::hasColumn('post_nummer', 'merinfo_personer_total')) {
                $table->integer('merinfo_personer_total')->nullable()->after('merinfo_foretag');
            }
            if (! Schema::hasColumn('post_nummer', 'merinfo_foretag_total')) {
                $table->integer('merinfo_foretag_total')->nullable()->after('merinfo_personer_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop total columns from merinfo_data table
        Schema::table('merinfo_data', function (Blueprint $table): void {
            if (Schema::hasColumn('merinfo_data', 'merinfo_foretag_total')) {
                $table->dropColumn('merinfo_foretag_total');
            }
            if (Schema::hasColumn('merinfo_data', 'merinfo_personer_total')) {
                $table->dropColumn('merinfo_personer_total');
            }
        });

        // Drop total columns from post_nummer table
        Schema::table('post_nummer', function (Blueprint $table): void {
            if (Schema::hasColumn('post_nummer', 'merinfo_foretag_total')) {
                $table->dropColumn('merinfo_foretag_total');
            }
            if (Schema::hasColumn('post_nummer', 'merinfo_personer_total')) {
                $table->dropColumn('merinfo_personer_total');
            }
        });
    }
};
