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
        Schema::table('hitta_se', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('link');
            $table->boolean('is_telefon')->default(false)->after('is_active');
            $table->boolean('is_ratsit')->default(false)->after('is_telefon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hitta_se', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_telefon', 'is_ratsit']);
        });
    }
};
