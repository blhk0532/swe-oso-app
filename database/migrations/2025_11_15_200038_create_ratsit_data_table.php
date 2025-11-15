<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratsit_data', function (Blueprint $table): void {
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
            $table->json('telfonnummer')->default(json_encode([])); // note: intentional column name per spec

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
            $table->json('telefon')->default(json_encode([]));

            // Emails
            $table->json('epost_adress')->default(json_encode([]));

            // Dwelling
            $table->text('agandeform')->nullable();
            $table->text('bostadstyp')->nullable();
            $table->text('boarea')->nullable();
            $table->text('byggar')->nullable();
            $table->text('fastighet')->nullable();

            // Collections
            $table->json('personer')->default(json_encode([]));
            $table->json('foretag')->default(json_encode([]));
            $table->json('grannar')->default(json_encode([]));
            $table->json('fordon')->default(json_encode([]));
            $table->json('hundar')->default(json_encode([]));
            $table->json('bolagsengagemang')->default(json_encode([]));

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
        Schema::dropIfExists('ratsit_data');
    }
};
