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
        Schema::create('schedules', function (Blueprint $table) {
            $table->string('id')->primary(); // JKT251213001
            $table->string('route_id');
            $table->foreign('route_id')->references('id')->on('routes');
            $table->string('bus_id');
            $table->foreign('bus_id')->references('id')->on('buses');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->decimal('price_per_seat', 10, 2);
            $table->integer('quota')->after('price_per_seat');
            $table->text('remarks')->nullable()->after('quota'); // Replaces status, using text for flexibility
            $table->timestamps();
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION set_schedule_id() RETURNS TRIGGER AS $$
            DECLARE
                dest_code text;
                period_key text;
                seq_num int;
            BEGIN
                -- We grab the destination code directly from the Route
                SELECT destination_code INTO dest_code FROM routes WHERE id = NEW.route_id;

                period_key := to_char(NEW.departure_time, 'YYMMDD');
                
                -- Key example: SCH_JKT_251213
                seq_num := get_next_date_sequence('SCH_' || dest_code || '_' || period_key);
                
                NEW.id := dest_code || period_key || LPAD(seq_num::text, 3, '0');
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_schedule_id BEFORE INSERT ON schedules FOR EACH ROW EXECUTE FUNCTION set_schedule_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_schedule_id ON schedules;
            DROP FUNCTION IF EXISTS set_schedule_id();
        ");
        Schema::dropIfExists('schedules');
    }
};
