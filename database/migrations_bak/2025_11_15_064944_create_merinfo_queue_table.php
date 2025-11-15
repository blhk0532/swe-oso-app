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
        Schema::create('merinfo_queue', function (Blueprint $table) {
            $table->id();
            $table->string('post_nummer');
            $table->string('post_ort');
            $table->string('post_lan');
            $table->integer('foretag_total')->default(0);
            $table->integer('personer_total')->default(0);
            $table->integer('foretag_phone')->default(0);
            $table->integer('personer_phone')->default(0);
            $table->integer('foretag_saved')->default(0);
            $table->integer('personer_saved')->default(0);
            $table->boolean('foretag_queued')->default(false);
            $table->boolean('personer_queued')->default(false);
            $table->boolean('foretag_scraped')->default(false);
            $table->boolean('personer_scraped')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merinfo_queue');
    }
};
