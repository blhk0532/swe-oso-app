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
        Schema::create('hitta_se', function (Blueprint $table) {
            $table->id();
            $table->text('personnamn')->nullable();
            $table->text('alder')->nullable();
            $table->text('kon')->nullable();
            $table->text('gatuadress')->nullable();
            $table->text('postnummer')->nullable();
            $table->text('postort')->nullable();
            $table->text('telefon')->nullable();
            $table->text('karta')->nullable();
            $table->text('link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitta_se');
    }
};
