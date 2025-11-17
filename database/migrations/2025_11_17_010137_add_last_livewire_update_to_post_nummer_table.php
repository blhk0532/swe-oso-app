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
        Schema::table('post_nummer', function (Blueprint $table) {
            $table->timestamp('last_livewire_update')->nullable()->after('merinfo_foretag_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_nummer', function (Blueprint $table) {
            $table->dropColumn('last_livewire_update');
        });
    }
};
