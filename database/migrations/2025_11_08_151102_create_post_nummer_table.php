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
        Schema::create('post_nummer', function (Blueprint $table) {
            $table->id();
            $table->string('post_nummer', 5)->unique();
            $table->string('post_ort')->nullable();
            $table->integer('total_count')->default(0);
            $table->boolean('is_pending')->default(true);
            $table->boolean('is_complete')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_nummer');
    }
};
