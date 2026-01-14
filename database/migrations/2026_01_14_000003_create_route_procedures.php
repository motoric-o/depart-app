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
            DROP PROCEDURE IF EXISTS sp_manage_route(TEXT, TEXT, TEXT, TEXT, INT, INT);

            -- 3.2 MANAGE ROUTE (Procedure)
            CREATE OR REPLACE PROCEDURE sp_manage_route(
                p_action TEXT,
                p_id TEXT,
                p_source TEXT,
                p_destination_code TEXT,
                p_distance INT,
                p_estimated_duration INT
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_source_code TEXT;
            BEGIN
                v_source_code := NULL;

                IF p_action = 'CREATE' THEN
                    INSERT INTO routes (source, source_code, destination_code, distance, estimated_duration, created_at, updated_at)
                    VALUES (p_source, v_source_code, p_destination_code, p_distance, p_estimated_duration, NOW(), NOW());
                ELSIF p_action = 'UPDATE' THEN
                    UPDATE routes SET 
                        source = p_source, source_code = v_source_code, destination_code = p_destination_code, 
                        distance = p_distance, estimated_duration = p_estimated_duration, updated_at = NOW()
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
            DROP PROCEDURE IF EXISTS sp_manage_route(TEXT, TEXT, TEXT, TEXT, INT, INT);
        ");
    }
};
