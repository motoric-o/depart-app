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
        Schema::create('tickets', function (Blueprint $table) {
            $table->string('id')->primary(); // SCHID-BUSID-01
            $table->string('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings');
            $table->string('passenger_name');
            $table->string('seat_number');
            $table->string('status')->default('Valid');
            $table->timestamps();
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION set_ticket_id() RETURNS TRIGGER AS $$
            DECLARE
                next_seq int;
                sch_id text;
                b_id text;
            BEGIN
                SELECT b.schedule_id, s.bus_id INTO sch_id, b_id 
                FROM bookings b JOIN schedules s ON b.schedule_id = s.id 
                WHERE b.id = NEW.booking_id;
                
                -- Count existing tickets for this specific schedule
                SELECT COALESCE(COUNT(*), 0) + 1 INTO next_seq 
                FROM tickets t JOIN bookings b ON t.booking_id = b.id 
                WHERE b.schedule_id = sch_id;
                
                NEW.id := sch_id || '-' || b_id || '-' || LPAD(next_seq::text, 2, '0');
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_ticket_id BEFORE INSERT ON tickets FOR EACH ROW EXECUTE FUNCTION set_ticket_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_ticket_id ON tickets;
            DROP FUNCTION IF EXISTS set_ticket_id();
        ");
        Schema::dropIfExists('tickets');
    }
};
