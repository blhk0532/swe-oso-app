<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hitta_bolag', function (Blueprint $table) {
            // Add new columns if missing
            if (! Schema::hasColumn('hitta_bolag', 'org_nr')) {
                $table->string('org_nr')->nullable()->after('personnamn');
            }

            if (! Schema::hasColumn('hitta_bolag', 'bolagsform')) {
                $table->string('bolagsform')->nullable()->after('org_nr');
            }

            if (! Schema::hasColumn('hitta_bolag', 'sni_branch')) {
                $table->json('sni_branch')->default(json_encode([]))->after('bolagsform');
            }
        });

        // Perform safe renames when the source columns exist
        Schema::table('hitta_bolag', function (Blueprint $table) {
            if (Schema::hasColumn('hitta_bolag', 'kon') && ! Schema::hasColumn('hitta_bolag', 'juridiskt_namn')) {
                $table->renameColumn('kon', 'juridiskt_namn');
            }

            if (Schema::hasColumn('hitta_bolag', 'alder') && ! Schema::hasColumn('hitta_bolag', 'registreringsdatum')) {
                $table->renameColumn('alder', 'registreringsdatum');
            }

            // If a legacy personnummer exists, rename it to org_nr
            if (Schema::hasColumn('hitta_bolag', 'personnummer') && ! Schema::hasColumn('hitta_bolag', 'org_nr')) {
                $table->renameColumn('personnummer', 'org_nr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hitta_bolag', function (Blueprint $table) {
            if (Schema::hasColumn('hitta_bolag', 'juridiskt_namn') && ! Schema::hasColumn('hitta_bolag', 'kon')) {
                $table->renameColumn('juridiskt_namn', 'kon');
            }

            if (Schema::hasColumn('hitta_bolag', 'registreringsdatum') && ! Schema::hasColumn('hitta_bolag', 'alder')) {
                $table->renameColumn('registreringsdatum', 'alder');
            }

            if (Schema::hasColumn('hitta_bolag', 'org_nr') && ! Schema::hasColumn('hitta_bolag', 'personnummer')) {
                // Only rename back if legacy column didn't exist
                $table->renameColumn('org_nr', 'personnummer');
            }

            if (Schema::hasColumn('hitta_bolag', 'sni_branch')) {
                $table->dropColumn('sni_branch');
            }

            if (Schema::hasColumn('hitta_bolag', 'bolagsform')) {
                $table->dropColumn('bolagsform');
            }

            // If org_nr was added (not renamed), drop it safely
            if (Schema::hasColumn('hitta_bolag', 'org_nr') && Schema::hasColumn('hitta_bolag', 'personnummer')) {
                // In this case, keep personnummer and drop org_nr
                $table->dropColumn('org_nr');
            }
        });
    }
};
