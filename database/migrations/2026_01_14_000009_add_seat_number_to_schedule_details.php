<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('schedule_details', function (Blueprint $table) {
            $table->string('seat_number')->nullable()->after('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('schedule_details', function (Blueprint $table) {
            $table->dropColumn('seat_number');
        });
    }
};
