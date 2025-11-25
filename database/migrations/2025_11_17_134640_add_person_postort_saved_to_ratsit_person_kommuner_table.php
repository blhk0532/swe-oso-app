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
        Schema::table('ratsit_person_kommuner', function (Blueprint $table) {
            $table->integer('post_ort_saved')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratsit_person_kommuner', function (Blueprint $table) {
            $table->dropColumn('post_ort_saved');
        });
    }
};
