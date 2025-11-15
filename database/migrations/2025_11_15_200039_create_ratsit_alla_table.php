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
        // Create ratsit_alla table (copy of ratsit_data)
        Schema::create('ratsit_alla', function (Blueprint $table) {
            $table->id();

            // Address
            $table->text('gatuadress')->nullable();
            $table->text('postnummer')->nullable();
            $table->text('postort')->nullable();
            $table->text('forsamling')->nullable();
            $table->text('kommun')->nullable();
            $table->text('lan')->nullable();
            $table->text('adressandring')->nullable();

            // Arrays / JSON
            $table->json('telfonnummer')->nullable();

            // Person fields
            $table->text('stjarntacken')->nullable();
            $table->text('fodelsedag')->nullable();
            $table->text('personnummer')->nullable();
            $table->text('alder')->nullable();
            $table->text('kon')->nullable();
            $table->text('civilstand')->nullable();
            $table->text('fornamn')->nullable();
            $table->text('efternamn')->nullable();
            $table->text('personnamn')->nullable();

            // Phones
            $table->json('telefon')->nullable();

            // Dwelling
            $table->text('agandeform')->nullable();
            $table->text('bostadstyp')->nullable();
            $table->text('boarea')->nullable();
            $table->text('byggar')->nullable();

            // Collections
            $table->json('personer')->nullable();
            $table->json('foretag')->nullable();
            $table->json('grannar')->nullable();
            $table->json('fordon')->nullable();
            $table->json('hundar')->nullable();
            $table->json('bolagsengagemang')->nullable();

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_alla');
    }
};
