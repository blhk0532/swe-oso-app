<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->json('telefon')->nullable();
            $table->text('karta')->nullable();
            $table->text('link')->nullable();
            $table->text('bostadstyp')->nullable();
            $table->text('bostadspris')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_telefon')->default(false);
            $table->boolean('is_ratsit')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hitta_se');
    }
};
