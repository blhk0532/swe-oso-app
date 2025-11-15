<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratsit_foretag_data', function (Blueprint $table): void {
            $table->id();

            // Address
            $table->text('gatuadress')->nullable();
            $table->text('postnummer')->nullable();
            $table->text('postort')->nullable();

            // Arrays / JSON
            $table->json('telfonnummer')->nullable(); // note: intentional column name per spec

            // Phones
            $table->json('telefon')->nullable();

            // Emails
            $table->json('epost_adress')->nullable();

            // Geo / Links
            $table->text('longitude')->nullable();
            $table->text('latitud')->nullable();
            $table->text('google_maps')->nullable();
            $table->text('google_streetview')->nullable();
            $table->text('ratsit_se')->nullable();

            // Flags
            $table->boolean('is_active')->default(true);

            // Timestamps with defaults
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratsit_foretag_data');
    }
};
