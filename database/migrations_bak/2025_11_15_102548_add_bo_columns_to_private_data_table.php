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
        Schema::table('private_data', function (Blueprint $table) {
            // Add legacy prefixed columns expected by older tests/clients
            if (! Schema::hasColumn('private_data', 'bo_gatuadress')) {
                $table->text('bo_gatuadress')->nullable()->after('gatuadress');
            }
            if (! Schema::hasColumn('private_data', 'bo_postnummer')) {
                $table->text('bo_postnummer')->nullable()->after('postnummer');
            }
            if (! Schema::hasColumn('private_data', 'bo_postort')) {
                $table->text('bo_postort')->nullable()->after('postort');
            }
            if (! Schema::hasColumn('private_data', 'bo_forsamling')) {
                $table->text('bo_forsamling')->nullable()->after('forsamling');
            }
            if (! Schema::hasColumn('private_data', 'bo_kommun')) {
                $table->text('bo_kommun')->nullable()->after('kommun');
            }
            if (! Schema::hasColumn('private_data', 'bo_lan')) {
                $table->text('bo_lan')->nullable()->after('lan');
            }

            // Person (ps_) prefixed fields
            if (! Schema::hasColumn('private_data', 'ps_fodelsedag')) {
                $table->text('ps_fodelsedag')->nullable()->after('fodelsedag');
            }
            if (! Schema::hasColumn('private_data', 'ps_personnummer')) {
                $table->text('ps_personnummer')->nullable()->after('personnummer');
            }
            if (! Schema::hasColumn('private_data', 'ps_alder')) {
                $table->text('ps_alder')->nullable()->after('alder');
            }
            if (! Schema::hasColumn('private_data', 'ps_kon')) {
                $table->text('ps_kon')->nullable()->after('kon');
            }
            if (! Schema::hasColumn('private_data', 'ps_civilstand')) {
                $table->text('ps_civilstand')->nullable()->after('civilstand');
            }
            if (! Schema::hasColumn('private_data', 'ps_fornamn')) {
                $table->text('ps_fornamn')->nullable()->after('fornamn');
            }
            if (! Schema::hasColumn('private_data', 'ps_efternamn')) {
                $table->text('ps_efternamn')->nullable()->after('efternamn');
            }
            if (! Schema::hasColumn('private_data', 'ps_personnamn')) {
                $table->text('ps_personnamn')->nullable()->after('personnamn');
            }

            // Contact fields
            if (! Schema::hasColumn('private_data', 'ps_telefon')) {
                $table->text('ps_telefon')->nullable()->after('telefon');
            }
            if (! Schema::hasColumn('private_data', 'ps_epost_adress')) {
                $table->text('ps_epost_adress')->nullable()->after('ps_telefon');
            }

            // Bolag/engagemang
            if (! Schema::hasColumn('private_data', 'ps_bolagsengagemang')) {
                $table->json('ps_bolagsengagemang')->nullable()->after('bolagsengagemang');
            }

            // Dwelling / ownership fields prefixed with bo_
            if (! Schema::hasColumn('private_data', 'bo_agandeform')) {
                $table->text('bo_agandeform')->nullable()->after('agandeform');
            }
            if (! Schema::hasColumn('private_data', 'bo_bostadstyp')) {
                $table->text('bo_bostadstyp')->nullable()->after('bostadstyp');
            }
            if (! Schema::hasColumn('private_data', 'bo_boarea')) {
                $table->text('bo_boarea')->nullable()->after('boarea');
            }
            if (! Schema::hasColumn('private_data', 'bo_byggar')) {
                $table->text('bo_byggar')->nullable()->after('byggar');
            }
            if (! Schema::hasColumn('private_data', 'bo_fastighet')) {
                $table->text('bo_fastighet')->nullable()->after('byggar');
            }

            // Collections / counts
            if (! Schema::hasColumn('private_data', 'bo_personer')) {
                $table->integer('bo_personer')->nullable()->after('personer');
            }
            if (! Schema::hasColumn('private_data', 'bo_foretag')) {
                $table->integer('bo_foretag')->nullable()->after('foretag');
            }
            if (! Schema::hasColumn('private_data', 'bo_grannar')) {
                $table->json('bo_grannar')->nullable()->after('grannar');
            }
            if (! Schema::hasColumn('private_data', 'bo_fordon')) {
                $table->json('bo_fordon')->nullable()->after('fordon');
            }
            if (! Schema::hasColumn('private_data', 'bo_hundar')) {
                $table->json('bo_hundar')->nullable()->after('hundar');
            }

            // Geo
            if (! Schema::hasColumn('private_data', 'bo_longitude')) {
                $table->text('bo_longitude')->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('private_data', 'bo_latitud')) {
                $table->text('bo_latitud')->nullable()->after('latitud');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('private_data', function (Blueprint $table) {
            $columns = [
                'bo_gatuadress', 'bo_postnummer', 'bo_postort', 'bo_forsamling', 'bo_kommun', 'bo_lan',
                'ps_fodelsedag', 'ps_personnummer', 'ps_alder', 'ps_kon', 'ps_civilstand', 'ps_fornamn', 'ps_efternamn', 'ps_personnamn',
                'ps_telefon', 'ps_epost_adress', 'ps_bolagsengagemang',
                'bo_agandeform', 'bo_bostadstyp', 'bo_boarea', 'bo_byggar', 'bo_fastighet',
                'bo_personer', 'bo_foretag', 'bo_grannar', 'bo_fordon', 'bo_hundar', 'bo_longitude', 'bo_latitud',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('private_data', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
