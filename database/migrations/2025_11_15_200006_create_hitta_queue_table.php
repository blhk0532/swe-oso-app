<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hitta_queue', function (Blueprint $table) {
            $table->id();
            $table->string('post_nummer');
            $table->string('post_ort');
            $table->string('post_lan');
            $table->integer('foretag_total')->default(0);
            $table->integer('personer_total')->default(0);
            $table->integer('foretag_phone')->default(0);
            $table->integer('personer_phone')->default(0);
            $table->integer('personer_house')->default(0);
            $table->integer('foretag_saved')->default(0);
            $table->integer('personer_saved')->default(0);
            $table->unsignedInteger('personer_pages')->default(0); // total pages detected
            $table->unsignedInteger('personer_page')->default(0);  // current/last processed page
            $table->enum('personer_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed'])->nullable()->default(null);
            $table->enum('foretag_status', ['pending', 'running', 'complete', 'empty', 'resume', 'idle', 'failed'])->nullable()->default(null);
            $table->boolean('foretag_queued')->default(false);
            $table->boolean('personer_queued')->default(false);
            $table->boolean('foretag_scraped')->default(false);
            $table->boolean('personer_scraped')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hitta_queue');
    }
};
