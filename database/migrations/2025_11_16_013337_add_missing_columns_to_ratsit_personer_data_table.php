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
        Schema::table('ratsit_personer_data', function (Blueprint $table) {
            $table->boolean('is_telefon')->default(false);
            $table->boolean('is_ratsit')->default(false);
            $table->boolean('is_hus')->default(false);
            $table->integer('ratsit_personer_total')->default(0);
            $table->integer('ratsit_foretag_total')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratsit_personer_data', function (Blueprint $table) {
            $table->dropColumn(['is_telefon', 'is_ratsit', 'is_hus', 'ratsit_personer_total', 'ratsit_foretag_total']);
        });
    }
};
