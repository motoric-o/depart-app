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
        Schema::create('buses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('bus_number')->unique();
            $table->string('bus_name')->nullable();
            $table->string('bus_type');
            $table->integer('capacity');

            $table->integer('seat_rows');
            $table->integer('seat_columns');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        DB::unprepared("
            -- CLEANUP FIRST
            DROP TRIGGER IF EXISTS trg_set_bus_id ON buses;
            DROP FUNCTION IF EXISTS set_bus_id();
            DROP SEQUENCE IF EXISTS buses_seq;

            -- CREATE FRESH
            CREATE SEQUENCE buses_seq;
            CREATE FUNCTION set_bus_id() RETURNS TRIGGER AS $$
            BEGIN
                NEW.id := 'BUS' || LPAD(nextval('buses_seq')::text, 3, '0');
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_bus_id BEFORE INSERT ON buses FOR EACH ROW EXECUTE FUNCTION set_bus_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_bus_id ON buses;
            DROP FUNCTION IF EXISTS set_bus_id();
            DROP SEQUENCE IF EXISTS buses_seq;
        ");
        Schema::dropIfExists('buses');
    }
};
