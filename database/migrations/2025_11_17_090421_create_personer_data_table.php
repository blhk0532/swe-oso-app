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
        Schema::create('personer_data', function (Blueprint $table) {
            $table->id();
            $table->string('personnamn', 255)->nullable();
            $table->string('gatuadress', 255)->nullable();
            $table->text('postnummer')->nullable();
            $table->text('postort')->nullable();
           
            $table->integer('hitta_data_id')->nullable();
            $table->text('hitta_personnamn')->nullable();
            $table->text('hitta_gatuadress')->nullable();
            $table->text('hitta_postnummer')->nullable();
            $table->text('hitta_postort')->nullable();     
            $table->text('hitta_alder')->nullable();
            $table->text('hitta_kon')->nullable();
            $table->text('hitta_telefon')->nullable();
            $table->text('hitta_telefonnummer')->nullable();
            $table->text('hitta_karta')->nullable();
            $table->text('hitta_link')->nullable();
            $table->text('hitta_bostadstyp')->nullable();
            $table->text('hitta_bostadspris')->nullable();
            $table->boolean('hitta_is_active')->default(false);
            $table->boolean('hitta_is_telefon')->default(false);
            $table->boolean('hitta_is_hus')->default(false);
            $table->timestamp('hitta_created_at')->nullable();
            $table->timestamp('hitta_updated_at')->nullable();  

            $table->integer('merinfo_data_id')->nullable();
            $table->text('merinfo_personnamn')->nullable();
            $table->text('merinfo_alder')->nullable();
            $table->text('merinfo_kon')->nullable();
            $table->text('merinfo_gatuadress')->nullable();
            $table->text('merinfo_postnummer')->nullable();
            $table->text('merinfo_postort')->nullable();
            $table->json('merinfo_telefon')->nullable();
            $table->text('merinfo_karta')->nullable();
            $table->text('merinfo_link')->nullable();
            $table->text('merinfo_bostadstyp')->nullable();
            $table->text('merinfo_bostadspris')->nullable();
            $table->boolean('merinfo_is_active')->default(false);
            $table->boolean('merinfo_is_telefon')->default(false);
            $table->boolean('merinfo_is_hus')->default(false);
            $table->timestamp('merinfo_created_at')->nullable();
            $table->timestamp('merinfo_updated_at')->nullable();

            $table->integer('ratsit_data_id')->nullable();
            $table->text('ratsit_gatuadress')->nullable();
            $table->text('ratsit_postnummer')->nullable();
            $table->text('ratsit_postort')->nullable();
            $table->text('ratsit_forsamling')->nullable();
            $table->text('ratsit_kommun')->nullable();
            $table->text('ratsit_lan')->nullable();
            $table->text('ratsit_adressandring')->nullable();
            $table->text('ratsit_kommun_ratsit')->nullable();
            $table->text('ratsit_stjarntacken')->nullable();
            $table->text('ratsit_fodelsedag')->nullable();
            $table->text('ratsit_personnummer')->nullable();
            $table->text('ratsit_alder')->nullable();
            $table->text('ratsit_kon')->nullable();
            $table->text('ratsit_civilstand')->nullable();
            $table->text('ratsit_fornamn')->nullable();
            $table->text('ratsit_efternamn')->nullable();
            $table->text('ratsit_personnamn')->nullable();
            $table->text('ratsit_agandeform')->nullable();
            $table->text('ratsit_bostadstyp')->nullable();
            $table->text('ratsit_boarea')->nullable();
            $table->text('ratsit_byggar')->nullable();
            $table->text('ratsit_fastighet')->nullable();
            $table->json('ratsit_telfonnummer')->nullable();
            $table->json('ratsit_epost_adress')->nullable();
            $table->json('ratsit_personer')->nullable();
            $table->json('ratsit_foretag')->nullable();
            $table->json('ratsit_grannar')->nullable();
            $table->json('ratsit_fordon')->nullable();
            $table->json('ratsit_hundar')->nullable();
            $table->json('ratsit_bolagsengagemang')->nullable();
            $table->text('ratsit_longitude')->nullable();
            $table->text('ratsit_latitud')->nullable();
            $table->text('ratsit_google_maps')->nullable();
            $table->text('ratsit_google_streetview')->nullable();
            $table->text('ratsit_ratsit_se')->nullable();
            $table->boolean('ratsit_is_active')->default(false);
            $table->boolean('ratsit_is_telefon')->default(false);
            $table->boolean('ratsit_is_hus')->default(false);
            $table->timestamp('ratsit_created_at')->nullable();
            $table->timestamp('ratsit_updated_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicate personnamn + gatuadress combinations
            $table->unique(['gatuadress', 'personnamn'], 'unique_gatuadress_personnamn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personer_data');
    }
};
