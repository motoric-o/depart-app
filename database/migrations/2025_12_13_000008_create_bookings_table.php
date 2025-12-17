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
        Schema::create('bookings', function (Blueprint $table) {
            $table->string('id')->primary(); // BK-2025-00001
            $table->string('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->string('schedule_id');
            $table->foreign('schedule_id')->references('id')->on('schedules');
            $table->dateTime('booking_date');
            $table->date('travel_date'); // Date when the trip will take place
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('Pending');
            $table->timestamps();
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION set_booking_id() RETURNS TRIGGER AS $$
            DECLARE
                period_key text;
                seq_num int;
            BEGIN
                period_key := to_char(NOW(), 'YYYY');
                
                -- Key example: BK_2025
                seq_num := get_next_date_sequence('BK_' || period_key);
                
                NEW.id := 'BK-' || period_key || '-' || LPAD(seq_num::text, 5, '0');
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_booking_id BEFORE INSERT ON bookings FOR EACH ROW EXECUTE FUNCTION set_booking_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_booking_id ON bookings;
            DROP FUNCTION IF EXISTS set_booking_id();
        ");
        Schema::dropIfExists('bookings');
    }
};
