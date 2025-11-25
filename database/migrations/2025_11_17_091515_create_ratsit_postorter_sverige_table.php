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
        Schema::create('ratsit_postorter_sverige', function (Blueprint $table) {
            $table->id();
            $table->string('post_ort', 255);
            $table->string('post_nummer', 10);
            $table->integer('post_nummer_count')->default(0);
            $table->string('post_nummer_link', 500);
            $table->timestamps();

            $table->index('post_ort');
            $table->index('post_nummer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_postorter_sverige');
    }
};
