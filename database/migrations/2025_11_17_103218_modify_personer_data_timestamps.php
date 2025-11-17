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
        Schema::table('personer_data', function (Blueprint $table) {
            // Drop existing timestamp columns
            $table->dropColumn(['hitta_created_at', 'hitta_updated_at', 'merinfo_created_at', 'merinfo_updated_at', 'ratsit_created_at', 'ratsit_updated_at']);
        });

        Schema::table('personer_data', function (Blueprint $table) {
            // Recreate timestamp columns as nullable without automatic updates
            $table->timestamp('hitta_created_at')->nullable();
            $table->timestamp('hitta_updated_at')->nullable();
            $table->timestamp('merinfo_created_at')->nullable();
            $table->timestamp('merinfo_updated_at')->nullable();
            $table->timestamp('ratsit_created_at')->nullable();
            $table->timestamp('ratsit_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personer_data', function (Blueprint $table) {
            // Drop the nullable timestamp columns
            $table->dropColumn(['hitta_created_at', 'hitta_updated_at', 'merinfo_created_at', 'merinfo_updated_at', 'ratsit_created_at', 'ratsit_updated_at']);
        });

        Schema::table('personer_data', function (Blueprint $table) {
            // Recreate with automatic timestamps (original behavior)
            $table->timestamp('hitta_created_at')->useCurrent();
            $table->timestamp('hitta_updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('merinfo_created_at')->nullable();
            $table->timestamp('merinfo_updated_at')->useCurrentOnUpdate();
            $table->timestamp('ratsit_created_at')->nullable()->useCurrent();
            $table->timestamp('ratsit_updated_at')->nullable()->useCurrentOnUpdate();
        });
    }
};
