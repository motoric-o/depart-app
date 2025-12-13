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
        // 1. Create Counter Table for Date-Based IDs
        Schema::create('sequence_counters', function (Blueprint $table) {
            $table->string('key')->primary(); 
            $table->integer('last_value');
            $table->timestamps();
        });

        // 2. Function: Get Next Sequence (Resets Automatically)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION get_next_date_sequence(p_key TEXT) 
            RETURNS INT AS $$
            DECLARE
                v_val INT;
            BEGIN
                INSERT INTO sequence_counters (key, last_value, created_at, updated_at)
                VALUES (p_key, 1, NOW(), NOW())
                ON CONFLICT (key) DO UPDATE 
                SET last_value = sequence_counters.last_value + 1, updated_at = NOW()
                RETURNING last_value INTO v_val;
                RETURN v_val;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 3. Function: Standard Sequence for Non-Date IDs (Buses)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_custom_id(seq_name text, prefix text, padding int) 
            RETURNS text AS $$
            DECLARE
                next_val bigint;
            BEGIN
                next_val := nextval(seq_name);
                RETURN prefix || LPAD(next_val::text, padding, '0');
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS get_next_date_sequence(TEXT);
            DROP FUNCTION IF EXISTS generate_custom_id(text, text, int);
        ");
        Schema::dropIfExists('sequence_counters');
    }
};
