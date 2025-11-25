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
        Schema::create('ratsit_adresser_sverige', function (Blueprint $table) {
            $table->id();
            $table->string('post_ort');
            $table->string('post_nummer');
            $table->string('gatuadress_namn');
            $table->integer('gatuadress_count');
            $table->string('gatuadress_nummer_link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_adresser_sverige');
    }
};
