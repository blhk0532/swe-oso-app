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
        $driver = DB::getDriverName();
        $isPostgres = $driver === 'pgsql';

        Schema::create('ratsit_data', function (Blueprint $table) use ($isPostgres) {
            $table->id();

            // Address fields
            $table->text('bo_gatuadress')->nullable();
            $table->string('bo_postnummer')->nullable();
            $table->string('bo_postort')->nullable();
            $table->string('bo_forsamling')->nullable();
            $table->string('bo_kommun')->nullable();
            $table->string('bo_lan')->nullable();

            // Person fields
            $table->date('ps_fodelsedag')->nullable();
            $table->string('ps_personnummer')->nullable();
            $table->string('ps_alder')->nullable();
            $table->string('ps_kon')->nullable();
            $table->string('ps_civilstand')->nullable();
            $table->string('ps_fornamn')->nullable();
            $table->string('ps_efternamn')->nullable();
            $table->text('ps_personnamn')->nullable();

            // JSON/JSONB array fields
            if ($isPostgres) {
                $table->jsonb('ps_telefon')->default('[]');
                $table->jsonb('ps_epost_adress')->default('[]');
                $table->jsonb('ps_bolagsengagemang')->default('[]');
                $table->jsonb('bo_personer')->default('[]');
                $table->jsonb('bo_foretag')->default('[]');
                $table->jsonb('bo_grannar')->default('[]');
                $table->jsonb('bo_fordon')->default('[]');
                $table->jsonb('bo_hundar')->default('[]');
            } else {
                $table->json('ps_telefon')->default('[]');
                $table->json('ps_epost_adress')->default('[]');
                $table->json('ps_bolagsengagemang')->default('[]');
                $table->json('bo_personer')->default('[]');
                $table->json('bo_foretag')->default('[]');
                $table->json('bo_grannar')->default('[]');
                $table->json('bo_fordon')->default('[]');
                $table->json('bo_hundar')->default('[]');
            }

            // Address property fields
            $table->string('bo_agandeform')->nullable();
            $table->string('bo_bostadstyp')->nullable();
            $table->string('bo_boarea')->nullable();
            $table->string('bo_byggar')->nullable();
            $table->string('bo_fastighet')->nullable();

            // Geographic coordinates
            $table->decimal('bo_longitude', 10, 7)->nullable();
            $table->decimal('bo_latitud', 10, 7)->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();
        });

        // Indexes
        Schema::table('ratsit_data', function (Blueprint $table) {
            $table->index('bo_postnummer');
            $table->index('bo_postort');
            $table->index('bo_kommun');
            $table->index('bo_lan');
            $table->index('ps_personnummer');
            $table->index('ps_personnamn');
            $table->index('bo_agandeform');
            $table->index('bo_bostadstyp');
            $table->index('is_active');
        });

        DB::statement('CREATE INDEX idx_ratsit_data_postnummer_postort ON ratsit_data(bo_postnummer, bo_postort)');
        DB::statement('CREATE INDEX idx_ratsit_data_kommun_lan ON ratsit_data(bo_kommun, bo_lan)');

        if ($isPostgres) {
            DB::statement('CREATE INDEX idx_ratsit_data_ps_telefon_gin ON ratsit_data USING GIN (ps_telefon)');
            DB::statement('CREATE INDEX idx_ratsit_data_ps_epost_gin ON ratsit_data USING GIN (ps_epost_adress)');
            DB::statement('CREATE INDEX idx_ratsit_data_active ON ratsit_data(is_active) WHERE is_active = true');
            DB::statement('CREATE INDEX idx_ratsit_data_personnamn_fts ON ratsit_data USING GIN (to_tsvector(\'swedish\', ps_personnamn))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratsit_data');
    }
};
