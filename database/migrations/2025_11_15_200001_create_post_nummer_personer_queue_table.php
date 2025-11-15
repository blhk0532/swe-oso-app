<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_nummer_personer_queue', function (Blueprint $table) {
            $table->id();
            $table->string('post_nummer', 5)->unique();
            $table->string('post_ort')->nullable();
            $table->string('post_lan')->nullable();

            // Merinfo metrics
            $table->integer('merinfo_personer_saved')->nullable();
            $table->integer('merinfo_personer_total')->nullable();
            $table->enum('merinfo_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);
            // Ratsit metrics
            $table->integer('ratsit_personer_saved')->nullable();
            $table->integer('ratsit_personer_total')->nullable();
            $table->enum('ratsit_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);

            // Hitta metrics
            $table->integer('hitta_personer_saved')->nullable();
            $table->integer('hitta_personer_total')->nullable();
            $table->enum('hitta_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);

            $table->integer('post_nummer_personer_saved')->nullable();
            $table->integer('post_nummer_personer_total')->nullable();
            $table->enum('post_nummer_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);

            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_nummer_personer_queue');
    }
};
