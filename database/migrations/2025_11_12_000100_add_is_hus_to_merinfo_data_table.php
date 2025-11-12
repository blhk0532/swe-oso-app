<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merinfo_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('merinfo_data', 'is_hus')) {
                $table->boolean('is_hus')->default(false)->after('is_ratsit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('merinfo_data', function (Blueprint $table): void {
            if (Schema::hasColumn('merinfo_data', 'is_hus')) {
                $table->dropColumn('is_hus');
            }
        });
    }
};
