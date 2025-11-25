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
        Schema::create('ratsit_kommuner_sverige', function (Blueprint $table) {
            $table->id();
            $table->string('kommun')->unique();
            $table->integer('post_ort_saved')->default(0);
            $table->integer('personer_total')->default(0);
            $table->string('ratsit_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_kommuner_sverige');
    }
};
