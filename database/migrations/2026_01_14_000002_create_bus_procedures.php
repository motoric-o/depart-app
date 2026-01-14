<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::unprepared("
            -- DROP IF EXISTS to ensure clean slate if run out of order or re-run
            DROP PROCEDURE IF EXISTS sp_manage_bus(TEXT, TEXT, TEXT, TEXT, INT, INT, INT, TEXT);

            -- 3.1 MANAGE BUS (Procedure)
            CREATE OR REPLACE PROCEDURE sp_manage_bus(
                p_action TEXT, -- 'CREATE' or 'UPDATE'
                p_id TEXT, -- Null for CREATE
                p_bus_number TEXT,
                p_bus_type TEXT,
                p_capacity INT,
                p_seat_rows INT,
                p_seat_columns INT,
                p_remarks TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                IF p_action = 'CREATE' THEN
                    INSERT INTO buses (bus_number, bus_type, capacity, seat_rows, seat_columns, remarks, created_at, updated_at)
                    VALUES (p_bus_number, p_bus_type, p_capacity, p_seat_rows, p_seat_columns, p_remarks, NOW(), NOW());
                ELSIF p_action = 'UPDATE' THEN
                    UPDATE buses SET 
                        bus_number = p_bus_number, bus_type = p_bus_type, capacity = p_capacity, 
                        seat_rows = p_seat_rows, seat_columns = p_seat_columns, 
                        remarks = p_remarks, updated_at = NOW()
                    WHERE id = p_id;
                END IF;
                COMMIT;
            END;
            $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_manage_bus(TEXT, TEXT, TEXT, TEXT, INT, INT, INT, TEXT);
        ");
    }
};
