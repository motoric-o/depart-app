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
            DROP PROCEDURE IF EXISTS sp_manage_destination(TEXT, TEXT, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_delete_destination(TEXT);

            -- 1. MANAGE DESTINATION (CREATE/UPDATE)
            CREATE OR REPLACE PROCEDURE sp_manage_destination(
                p_action TEXT, -- 'CREATE' or 'UPDATE'
                p_current_code TEXT, -- specific for UPDATE filter
                p_new_code TEXT,
                p_city_name TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                IF p_action = 'CREATE' THEN
                    INSERT INTO destinations (code, city_name, created_at, updated_at)
                    VALUES (p_new_code, p_city_name, NOW(), NOW());
                    
                ELSIF p_action = 'UPDATE' THEN
                    UPDATE destinations 
                    SET code = p_new_code, 
                        city_name = p_city_name, 
                        updated_at = NOW() 
                    WHERE code = p_current_code;
                END IF;
            END;
            $$;

            -- 2. DELETE DESTINATION
            CREATE OR REPLACE PROCEDURE sp_delete_destination(p_code TEXT)
            LANGUAGE plpgsql AS $$
            DECLARE
                v_route_count INT;
            BEGIN
                -- Check if used in routes
                SELECT COUNT(*) INTO v_route_count 
                FROM routes 
                WHERE source_code = p_code OR destination_code = p_code;
                
                IF v_route_count > 0 THEN
                    RAISE EXCEPTION 'Cannot delete destination. It is used in % route(s).', v_route_count;
                END IF;

                DELETE FROM destinations WHERE code = p_code;
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
            DROP PROCEDURE IF EXISTS sp_manage_destination(TEXT, TEXT, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_delete_destination(TEXT);
        ");
    }
};
