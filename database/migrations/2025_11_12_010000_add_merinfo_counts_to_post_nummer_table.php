<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_nummer', function (Blueprint $table): void {
            if (! Schema::hasColumn('post_nummer', 'merinfo_personer')) {
                $table->integer('merinfo_personer')->nullable()->after('personer');
            }
            if (! Schema::hasColumn('post_nummer', 'merinfo_foretag')) {
                $table->integer('merinfo_foretag')->nullable()->after('merinfo_personer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_nummer', function (Blueprint $table): void {
            if (Schema::hasColumn('post_nummer', 'merinfo_foretag')) {
                $table->dropColumn('merinfo_foretag');
            }
            if (Schema::hasColumn('post_nummer', 'merinfo_personer')) {
                $table->dropColumn('merinfo_personer');
            }
        });
    }
};
