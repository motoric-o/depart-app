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
        Schema::table('sessions', function (Blueprint $table) {
            // changing the column type to string
            $table->string('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Truncate to avoid casting errors on non-numeric IDs
         DB::table('sessions')->truncate();
         // Use raw statement to handle casting
         DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING user_id::bigint');
    }
};
