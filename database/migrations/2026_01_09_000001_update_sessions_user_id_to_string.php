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
        Schema::table('sessions', function (Blueprint $table) {
            // Reverting back to foreignId (unsignedBigInteger) might be tricky if there's string data.
            // But for correctness of down method:
             $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
