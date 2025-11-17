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
            // Create unique index with key length for TEXT columns (MySQL requirement)
            $table->index(['gatuadress(255)', 'personnamn(255)'], 'unique_gatuadress_personnamn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personer_data', function (Blueprint $table) {
            $table->dropIndex('unique_gatuadress_personnamn');
        });
    }
};
