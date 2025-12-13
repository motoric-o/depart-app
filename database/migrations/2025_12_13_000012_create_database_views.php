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
        DB::unprepared("
            CREATE OR REPLACE VIEW view_available_trips AS
            SELECT 
                s.id as schedule_id, 
                r.source, 
                r.destination_code, 
                d.city_name as destination_name,
                s.departure_time, 
                b.bus_type, 
                s.price_per_seat
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN destinations d ON r.destination_code = d.code
            JOIN buses b ON s.bus_id = b.id
            WHERE s.status = 'Scheduled';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS view_available_trips");
    }
};
