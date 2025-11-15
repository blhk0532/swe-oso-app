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
        Schema::table('merinfo_queue', function (Blueprint $table) {
            if (! Schema::hasColumn('merinfo_queue', 'personer_house')) {
                $table->integer('personer_house')->default(0)->after('personer_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merinfo_queue', function (Blueprint $table) {
            if (Schema::hasColumn('merinfo_queue', 'personer_house')) {
                $table->dropColumn('personer_house');
            }
        });
    }
};
