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
            if (! Schema::hasColumn('post_nummer', 'personer_house')) {
                $table->integer('personer_house')->nullable()->after('personer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_nummer', function (Blueprint $table) {
            if (Schema::hasColumn('post_nummer', 'personer_house')) {
                $table->dropColumn('personer_house');
            }
        });
    }
};
