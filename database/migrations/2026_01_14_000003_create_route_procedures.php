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
            END;
            $$;

            CREATE OR REPLACE PROCEDURE sp_delete_route(p_id TEXT)
            LANGUAGE plpgsql AS $$
            BEGIN
                DELETE FROM routes WHERE id = p_id;
            END;
            $$;

            CREATE OR REPLACE FUNCTION update_schedule_remarks_on_route_delete() RETURNS TRIGGER AS $$
            BEGIN
                UPDATE schedules 
                SET remarks = 'Cancelled',
                    route_source = OLD.source,
                    route_destination = (SELECT city_name FROM destinations WHERE code = OLD.destination_code)
                WHERE route_id = OLD.id;
                RETURN OLD;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trg_update_schedule_remarks_on_route_delete ON routes;
            CREATE TRIGGER trg_update_schedule_remarks_on_route_delete
            BEFORE DELETE ON routes
            FOR EACH ROW EXECUTE FUNCTION update_schedule_remarks_on_route_delete();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_update_schedule_remarks_on_route_delete ON routes;
            DROP FUNCTION IF EXISTS update_schedule_remarks_on_route_delete();
            DROP PROCEDURE IF EXISTS sp_delete_route(TEXT);
            DROP PROCEDURE IF EXISTS sp_manage_route(TEXT, TEXT, TEXT, TEXT, INT, INT);
        ");
    }
};
