<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratsit_que', function (Blueprint $table) {
            $table->id();

            // Address fields
            $table->text('gatuadress')->nullable();
            $table->string('postnummer')->nullable();
            $table->string('postort')->nullable();
            $table->string('forsamling')->nullable();
            $table->string('kommun')->nullable();
            $table->string('lan')->nullable();
            $table->string('adressandring')->nullable();

            // Phone fields (legacy and new)
            $table->json('telfonnummer')->default('[]');
            $table->json('telefon')->default('[]');

            // Person fields
            $table->string('stjarntacken')->nullable();
            $table->string('fodelsedag')->nullable();
            $table->string('personnummer')->nullable();
            $table->string('alder')->nullable();
            $table->string('kon')->nullable();
            $table->string('civilstand')->nullable();
            $table->string('fornamn')->nullable();
            $table->string('efternamn')->nullable();
            $table->text('personnamn')->nullable();

            // Contact
            $table->json('epost_adress')->default('[]');

            // Property fields
            $table->string('agandeform')->nullable();
            $table->string('bostadstyp')->nullable();
            $table->string('boarea')->nullable();
            $table->string('byggar')->nullable();
            $table->string('fastighet')->nullable();

            // JSON array fields
            $table->json('personer')->default('[]');
            $table->json('foretag')->default('[]');
            $table->json('grannar')->default('[]');
            $table->json('fordon')->default('[]');
            $table->json('hundar')->default('[]');
            $table->json('bolagsengagemang')->default('[]');

            // Geographic coordinates
            $table->string('longitude')->nullable();
            $table->string('latitud')->nullable();

            // Links
            $table->text('google_maps')->nullable();
            $table->text('google_streetview')->nullable();
            $table->text('ratsit_se')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();
        });

        // Indexes
        Schema::table('ratsit_que', function (Blueprint $table) {
            $table->index('postnummer');
            $table->index('postort');
            $table->index('kommun');
            $table->index('lan');
            $table->index('personnummer');
            $table->index('personnamn');
            $table->index('is_active');
        });

        DB::statement('CREATE INDEX idx_ratsit_que_postnummer_postort ON ratsit_que(postnummer, postort)');
        DB::statement('CREATE INDEX idx_ratsit_que_kommun_lan ON ratsit_que(kommun, lan)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_que');
    }
};
