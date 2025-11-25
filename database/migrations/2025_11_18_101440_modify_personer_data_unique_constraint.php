<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note: The old unique constraint was already dropped in a previous migration
        // We're switching to application-level duplicate prevention based on address
        // No database constraint needed - the controller handles uniqueness logic
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes to reverse - uniqueness is now handled at application level
    }
};
