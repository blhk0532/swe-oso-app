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
        Schema::create('ratsit_foretag_postorter', function (Blueprint $table) {
            $table->id();
            $table->string('post_ort');
            $table->string('post_nummer');
            $table->integer('foretag_count');
            $table->string('ratsit_link');
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
        Schema::dropIfExists('ratsit_foretag_postorter');
    }
};
