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
        if (Schema::hasTable('data_private')) {
            // Table already exists, skip creating and indexing
            return;
        }

        $driver = DB::getDriverName();
        $isPostgres = $driver === 'pgsql';

        Schema::create('data_private', function (Blueprint $table) use ($isPostgres) {
            $table->id(); // BIGSERIAL in PostgreSQL

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

            // JSON/JSONB array fields - use jsonb for PostgreSQL, json for others
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

        // Add indexes for searchable fields (B-tree indexes)
        Schema::table('data_private', function (Blueprint $table) {
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

        // Add composite indexes for common search combinations (best-effort, ignore if not supported by driver)
        try { DB::statement('CREATE INDEX idx_data_private_postnummer_postort ON data_private(bo_postnummer, bo_postort)'); } catch (Throwable $e) {}
        try { DB::statement('CREATE INDEX idx_data_private_kommun_lan ON data_private(bo_kommun, bo_lan)'); } catch (Throwable $e) {}

        // PostgreSQL-specific indexes (GIN indexes, partial indexes, full-text search)
        if ($isPostgres) {
            try { DB::statement('CREATE INDEX idx_data_private_ps_telefon_gin ON data_private USING GIN (ps_telefon)'); } catch (Throwable $e) {}
            try { DB::statement('CREATE INDEX idx_data_private_ps_epost_gin ON data_private USING GIN (ps_epost_adress)'); } catch (Throwable $e) {}
            try { DB::statement('CREATE INDEX idx_data_private_active ON data_private(is_active) WHERE is_active = true'); } catch (Throwable $e) {}
            try { DB::statement('CREATE INDEX idx_data_private_personnamn_fts ON data_private USING GIN (to_tsvector(\'swedish\', ps_personnamn))'); } catch (Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_private');
    }
};
