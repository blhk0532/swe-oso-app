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
        Schema::table('hitta_se', function (Blueprint $table) {
            $table->text('bostadstyp')->nullable()->after('link');
            $table->text('bostadspris')->nullable()->after('bostadstyp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hitta_se', function (Blueprint $table) {
            $table->dropColumn(['bostadstyp', 'bostadspris']);
        });
    }
};
