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
        Schema::create('post_nummer_que', function (Blueprint $table) {
            $table->id();
            $table->string('post_nummer', 5)->unique();
            $table->string('post_ort')->nullable();
            $table->string('post_lan')->nullable();
            $table->integer('total_count')->default(0);
            $table->integer('count')->default(0);
            $table->integer('phone')->default(0);
            $table->integer('house')->default(0);
            $table->integer('bolag')->default(0);
            $table->integer('foretag')->default(0);
            $table->integer('personer')->default(0);
            $table->integer('platser')->default(0);
            $table->string('status')->nullable();
            $table->integer('progress')->default(0);
            $table->boolean('is_pending')->default(true);
            $table->boolean('is_complete')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('last_processed_page')->nullable();
            $table->integer('processed_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_nummer_que');
    }
};
