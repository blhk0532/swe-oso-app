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
            if (! Schema::hasColumn('post_nummer', 'foretag')) {
                $table->integer('foretag')->default(0)->after('bolag');
            }
            if (! Schema::hasColumn('post_nummer', 'personer')) {
                $table->integer('personer')->default(0)->after('foretag');
            }
            if (! Schema::hasColumn('post_nummer', 'platser')) {
                $table->integer('platser')->default(0)->after('personer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_nummer', function (Blueprint $table) {
            if (Schema::hasColumn('post_nummer', 'platser')) {
                $table->dropColumn('platser');
            }
            if (Schema::hasColumn('post_nummer', 'personer')) {
                $table->dropColumn('personer');
            }
            if (Schema::hasColumn('post_nummer', 'foretag')) {
                $table->dropColumn('foretag');
            }
        });
    }
};
