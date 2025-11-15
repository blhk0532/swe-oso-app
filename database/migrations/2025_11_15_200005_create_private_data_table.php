<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_data', function (Blueprint $table): void {
            $table->id();

            // Address fields
            $table->text('gatuadress')->nullable();
            $table->text('postnummer')->nullable();
            $table->text('postort')->nullable();
            $table->text('forsamling')->nullable();
            $table->text('kommun')->nullable();
            $table->text('lan')->nullable();
            $table->text('adressandring')->nullable();

            // Legacy BO_ prefixed address mirrors
            $table->text('bo_gatuadress')->nullable();
            $table->text('bo_postnummer')->nullable();
            $table->text('bo_postort')->nullable();
            $table->text('bo_forsamling')->nullable();
            $table->text('bo_kommun')->nullable();
            $table->text('bo_lan')->nullable();

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

            // PS_ prefixed person mirrors
            $table->text('ps_fodelsedag')->nullable();
            $table->text('ps_personnummer')->nullable();
            $table->text('ps_alder')->nullable();
            $table->text('ps_kon')->nullable();
            $table->text('ps_civilstand')->nullable();
            $table->text('ps_fornamn')->nullable();
            $table->text('ps_efternamn')->nullable();
            $table->text('ps_personnamn')->nullable();
            // person phone numbers - store as JSON array
            // Store person phone numbers as JSON (nullable to avoid MySQL default restriction)
            $table->json('ps_telefon')->nullable();
            // person's email addresses - store as JSON array
            // Person email addresses as JSON (nullable to avoid default on JSON)
            $table->json('ps_epost_adress')->nullable();
            $table->json('ps_bolagsengagemang')->nullable();

            // Dwelling fields
            $table->text('agandeform')->nullable();
            $table->text('bostadstyp')->nullable();
            $table->text('boarea')->nullable();
            $table->text('byggar')->nullable();

            // BO_ prefixed dwelling mirrors
            $table->text('bo_agandeform')->nullable();
            $table->text('bo_bostadstyp')->nullable();
            $table->text('bo_boarea')->nullable();
            $table->text('bo_byggar')->nullable();
            $table->text('bo_fastighet')->nullable();
            // Modern plain fastighet field (mirrors Ratsit/Hitta fields) used by tests
            $table->text('fastighet')->nullable();

            // Collections (JSON arrays)
            $table->json('personer')->nullable();
            $table->json('foretag')->nullable();
            $table->json('grannar')->nullable();
            $table->json('fordon')->nullable();
            $table->json('hundar')->nullable();
            $table->json('bolagsengagemang')->nullable();
            // Modern email array storage for backward-compatible APIs and tests
            // Modern email array storage (nullable for MySQL compatibility)
            $table->json('epost_adress')->nullable();

            // BO_ prefixed collection/count mirrors
            $table->integer('bo_personer')->nullable();
            $table->integer('bo_foretag')->nullable();
            $table->json('bo_grannar')->nullable();
            $table->json('bo_fordon')->nullable();
            $table->json('bo_hundar')->nullable();

            // Geo & Links
            $table->text('longitude')->nullable();
            $table->text('latitud')->nullable();
            $table->text('google_maps')->nullable();
            $table->text('google_streetview')->nullable();
            $table->text('ratsit_link')->nullable();

            // BO_ prefixed geo mirrors
            $table->text('bo_longitude')->nullable();
            $table->text('bo_latitud')->nullable();

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

    public function down(): void
    {
        Schema::dropIfExists('private_data');
    }
};
