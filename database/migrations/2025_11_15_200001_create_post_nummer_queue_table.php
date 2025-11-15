<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_nummer_queue', function (Blueprint $table) {
            $table->id();
            // Allow '12345' or '123 45' (with a space) - use 6 characters
            $table->string('post_nummer', 6)->unique();
            $table->string('post_ort')->nullable();
            $table->string('post_lan')->nullable();

            // Merinfo metrics
            $table->integer('merinfo_personer_saved')->nullable();
            $table->integer('merinfo_foretag_saved')->nullable();
            $table->integer('merinfo_personer_total')->nullable();
            $table->integer('merinfo_foretag_total')->nullable();
            $table->enum('merinfo_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);
            $table->boolean('merinfo_checked')->default(false);
            $table->boolean('merinfo_queued')->default(false);
            $table->boolean('merinfo_scraped')->default(false);
            $table->boolean('merinfo_complete')->default(false);

            // Ratsit metrics
            $table->integer('ratsit_personer_saved')->nullable();
            $table->integer('ratsit_foretag_saved')->nullable();
            $table->integer('ratsit_personer_total')->nullable();
            $table->integer('ratsit_foretag_total')->nullable();
            $table->enum('ratsit_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);
            $table->boolean('ratsit_checked')->default(false);
            $table->boolean('ratsit_queued')->default(false);
            $table->boolean('ratsit_scraped')->default(false);
            $table->boolean('ratsit_complete')->default(false);

            // Hitta metrics
            $table->integer('hitta_personer_saved')->nullable();
            $table->integer('hitta_foretag_saved')->nullable();
            $table->integer('hitta_personer_total')->nullable();
            $table->integer('hitta_foretag_total')->nullable();
            $table->enum('hitta_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);
            $table->boolean('hitta_checked')->default(false);
            $table->boolean('hitta_queued')->default(false);
            $table->boolean('hitta_scraped')->default(false);
            $table->boolean('hitta_complete')->default(false);

            $table->integer('post_nummer_personer_saved')->nullable();
            $table->integer('post_nummer_foretag_saved')->nullable();
            $table->integer('post_nummer_personer_total')->nullable();
            $table->integer('post_nummer_foretag_total')->nullable();
            $table->enum('post_nummer_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed', 'checked', 'queued', 'scraped'])->nullable()->default(null);
            $table->boolean('post_nummer_checked')->default(false);
            $table->boolean('post_nummer_queued')->default(false);
            $table->boolean('post_nummer_scraped')->default(false);
            $table->boolean('post_nummer_complete')->default(false);

            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_nummer_queue');
    }
};
