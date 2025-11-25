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
        Schema::create('ratsit_foretag_kommuner', function (Blueprint $table) {
            $table->id();
            $table->string('kommun');
            $table->integer('foretag_count');
            $table->string('ratsit_link');
            $table->boolean('foretag_postort_saved')->default(false);
            $table->timestamps();

            $table->index('kommun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_foretag_kommuner');
    }
};
