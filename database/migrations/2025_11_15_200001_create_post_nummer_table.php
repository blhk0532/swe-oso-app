<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_nummer', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('post_nummer', 6)->unique(); // Changed from 5 to 6 for "XXX XX" format
            $table->string('post_ort')->nullable();
            $table->string('post_lan')->nullable();

            // Totals & counts
            $table->integer('total_count')->default(0);
            $table->integer('count')->default(0);

            // Status & progress
            $table->enum('status', ['pending', 'running', 'complete'])->nullable()->default(null);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedInteger('last_processed_page')->nullable();
            $table->unsignedInteger('processed_count')->nullable();

            // Derived metrics
            $table->integer('phone')->default(0);
            $table->integer('house')->default(0);
            $table->integer('bolag')->default(0);

            // Category counts
            $table->integer('foretag')->default(0);
            $table->integer('personer')->default(0);
            $table->integer('personer_house')->nullable();
            $table->integer('platser')->default(0);

            // Merinfo metrics
            $table->integer('merinfo_personer')->nullable();
            $table->integer('merinfo_foretag')->nullable();
            $table->integer('merinfo_personer_total')->nullable();
            $table->integer('merinfo_foretag_total')->nullable();

            // Active flag
            $table->boolean('is_pending')->default(true);
            $table->boolean('is_complete')->default(false);
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_nummer');
    }
};
