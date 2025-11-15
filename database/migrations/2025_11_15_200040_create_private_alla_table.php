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
        // Create private_alla table (copy of private_data)
        Schema::create('private_alla', function (Blueprint $table) {
            $table->id();

            // Address fields
            $table->text('gatuadress')->nullable();
            $table->text('postnummer')->nullable();
            $table->text('postort')->nullable();
            $table->text('forsamling')->nullable();
            $table->text('kommun')->nullable();
            $table->text('lan')->nullable();
            $table->text('adressandring')->nullable();

            // Phone arrays
            $table->json('telfonnummer')->nullable();
            $table->json('telefon')->nullable();

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

            // Dwelling fields
            $table->text('agandeform')->nullable();
            $table->text('bostadstyp')->nullable();
            $table->text('boarea')->nullable();
            $table->text('byggar')->nullable();

            // Collections (JSON arrays)
            $table->json('personer')->nullable();
            $table->json('foretag')->nullable();
            $table->json('grannar')->nullable();
            $table->json('fordon')->nullable();
            $table->json('hundar')->nullable();
            $table->json('bolagsengagemang')->nullable();

            // Geo & Links
            $table->text('longitude')->nullable();
            $table->text('latitud')->nullable();
            $table->text('google_maps')->nullable();
            $table->text('google_streetview')->nullable();
            $table->text('ratsit_link')->nullable();

            // Hitta specific fields
            $table->text('hitta_link')->nullable();
            $table->text('hitta_karta')->nullable();
            $table->text('bostad_typ')->nullable();
            $table->text('bostad_pris')->nullable();

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_update')->default(false);

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_alla');
    }
};
