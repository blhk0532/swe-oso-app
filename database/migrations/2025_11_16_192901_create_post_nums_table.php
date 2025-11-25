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
        Schema::create('post_nums', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('post_nummer');
            $table->string('post_ort');
            $table->string('post_lan');
            $table->integer('merinfo_personer_total')->nullable()->default(null);
            $table->integer('merinfo_foretag_total')->nullable()->default(null);
            $table->integer('hitta_personer_total')->nullable()->default(null);
            $table->integer('hitta_foretag_total')->nullable()->default(null);
            $table->integer('ratsit_personer_total')->nullable()->default(null);
            $table->integer('ratsit_foretag_total')->nullable()->default(null);
            $table->string('status')->nullable()->default('idle');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('post_ort');
            $table->index('post_nummer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_nums');
    }
};
