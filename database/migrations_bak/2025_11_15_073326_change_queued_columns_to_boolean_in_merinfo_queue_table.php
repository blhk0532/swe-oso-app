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
            $table->boolean('foretag_queued')->default(false)->change();
            $table->boolean('personer_queued')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merinfo_queue', function (Blueprint $table) {
            $table->integer('foretag_queued')->default(0)->change();
            $table->integer('personer_queued')->default(0)->change();
        });
    }
};
