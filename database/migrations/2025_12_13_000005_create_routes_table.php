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
        Schema::create('routes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('source');
            $table->string('source_code')->nullable();
            $table->foreign('source_code')->references('code')->on('destinations')->onUpdate('cascade');
            $table->string('destination_code');
            $table->foreign('destination_code')->references('code')->on('destinations')->onUpdate('cascade');
            $table->integer('distance')->nullable();
            $table->integer('estimated_duration')->nullable();
            $table->timestamps();
        });

        DB::unprepared("
            -- CLEANUP FIRST
            DROP TRIGGER IF EXISTS trg_set_route_id ON routes;
            DROP FUNCTION IF EXISTS set_route_id();
            DROP SEQUENCE IF EXISTS routes_seq;

            -- CREATE FRESH
            CREATE SEQUENCE routes_seq;
            CREATE FUNCTION set_route_id() RETURNS TRIGGER AS $$
            BEGIN
                NEW.id := generate_custom_id('routes_seq', 'RTE-', 3);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_route_id BEFORE INSERT ON routes FOR EACH ROW EXECUTE FUNCTION set_route_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_route_id ON routes;
            DROP FUNCTION IF EXISTS set_route_id();
            DROP SEQUENCE IF EXISTS routes_seq;
        ");
        Schema::dropIfExists('routes');
    }
};
