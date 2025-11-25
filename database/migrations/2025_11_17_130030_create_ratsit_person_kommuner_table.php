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
        Schema::create('ratsit_person_kommuner', function (Blueprint $table) {
            $table->id();
            $table->string('kommun');
            $table->integer('person_count');
            $table->string('ratsit_link');
            $table->boolean('person_postort_saved')->default(false);
            $table->timestamps();

            $table->index('kommun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_person_kommuner');
    }
};
