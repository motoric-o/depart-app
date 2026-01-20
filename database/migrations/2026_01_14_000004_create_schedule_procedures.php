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
            DROP FUNCTION IF EXISTS sp_check_schedule_conflict(TEXT, TIMESTAMP, TIMESTAMP);
            DROP PROCEDURE IF EXISTS sp_check_schedule_conflict(TEXT, TIMESTAMP, TIMESTAMP, BOOLEAN);
            DROP PROCEDURE IF EXISTS sp_create_schedule(TEXT, TEXT, TIMESTAMP, TIMESTAMP, DECIMAL, INT);
            DROP PROCEDURE IF EXISTS sp_update_schedule_remarks(TEXT, TEXT);
            DROP FUNCTION IF EXISTS sp_search_trips(TEXT, TEXT, DATE, DECIMAL, DECIMAL);

            -- 1.2 CHECK SCHEDULE CONFLICT (Procedure)
            CREATE OR REPLACE PROCEDURE sp_check_schedule_conflict(
                p_bus_id TEXT, 
                p_departure TIMESTAMP, 
                p_arrival TIMESTAMP,
                INOUT p_conflict BOOLEAN
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_count INT;
            BEGIN
                SELECT COUNT(*) INTO v_count
                FROM schedules
                WHERE bus_id = p_bus_id
                AND (remarks IS NULL OR remarks != 'Cancelled')
                AND (
                    (departure_time <= p_departure AND arrival_time >= p_departure) OR
                    (departure_time <= p_arrival AND arrival_time >= p_arrival) OR
                    (departure_time >= p_departure AND arrival_time <= p_arrival)
                );
                
                p_conflict := (v_count > 0);
            END;
            $$;

            -- 3.3 CREATE SCHEDULE (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_schedule(
                p_route_id TEXT,
                p_bus_id TEXT,
                p_driver_id TEXT,
                p_departure_time TIMESTAMP,
                p_arrival_time TIMESTAMP,
                p_price_per_seat DECIMAL,
                p_quota INT
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_conflict BOOLEAN;
            BEGIN
                CALL sp_check_schedule_conflict(p_bus_id, p_departure_time, p_arrival_time, v_conflict);
                
                IF v_conflict THEN
                    RAISE EXCEPTION 'Bus is already scheduled for this time range.';
                END IF;

                INSERT INTO schedules (route_id, bus_id, driver_id, departure_time, arrival_time, price_per_seat, quota, remarks, created_at, updated_at)
                VALUES (p_route_id, p_bus_id, p_driver_id, p_departure_time, p_arrival_time, p_price_per_seat, p_quota, 'Scheduled', NOW(), NOW());
                
                COMMIT;
            END;
            $$;

            -- 3.4 UPDATE SCHEDULE REMARKS (Procedure)
            CREATE OR REPLACE PROCEDURE sp_update_schedule_remarks(p_id TEXT, p_remarks TEXT)
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE schedules SET remarks = p_remarks, updated_at = NOW() WHERE id = p_id;
                COMMIT;
            END;
            $$;

            -- 5.3 SEARCH TRIPS (Function)
            CREATE OR REPLACE FUNCTION sp_search_trips(
                p_source_code TEXT, 
                p_dest_code TEXT, 
                p_date DATE, 
                p_min_price DECIMAL DEFAULT 0, 
                p_max_price DECIMAL DEFAULT 99999999
            )
            RETURNS TABLE (
                schedule_id TEXT,
                source TEXT,
                destination TEXT,
                departure_time TIMESTAMP,
                arrival_time TIMESTAMP,
                price DECIMAL,
                bus_type TEXT,
                image_url TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                RETURN QUERY
                SELECT 
                    s.id::TEXT,
                    r.source::TEXT,
                    d.city_name::TEXT,
                    s.departure_time::TIMESTAMP,
                    s.arrival_time::TIMESTAMP,
                    s.price_per_seat::DECIMAL,
                    b.bus_type::TEXT,
                    b.bus_number::TEXT
                FROM schedules s
                JOIN routes r ON s.route_id = r.id
                JOIN destinations d ON r.destination_code = d.code
                JOIN buses b ON s.bus_id = b.id
                WHERE (r.source_code = p_source_code OR r.source LIKE '%' || p_source_code || '%')
                AND (p_dest_code = '' OR p_dest_code IS NULL OR r.destination_code = p_dest_code)
                AND (s.remarks IS NULL OR s.remarks = 'Scheduled')
                AND DATE(s.departure_time) >= p_date
                AND s.price_per_seat BETWEEN p_min_price AND p_max_price
                ORDER BY s.departure_time ASC;
            END;
            $$;
        ");

        /* Add Trigger for Schedule Auto-Updates (Manual Nulling) */
        DB::unprepared("
             CREATE OR REPLACE FUNCTION sp_auto_update_schedule_remarks() RETURNS TRIGGER AS $$
             BEGIN
                 -- If Bus is unassigned (Manual Update)
                 IF NEW.bus_id IS NULL AND (OLD.bus_id IS NOT NULL OR TG_OP = 'INSERT') THEN
                     NEW.remarks := 'Pending Bus Assignment';
                 END IF;

                 -- If Route is unassigned (Manual Update)
                 IF NEW.route_id IS NULL AND (OLD.route_id IS NOT NULL OR TG_OP = 'INSERT') THEN
                     NEW.remarks := 'Cancelled';
                     
                     -- Try to snapshot old route info if available
                     IF TG_OP = 'UPDATE' AND OLD.route_id IS NOT NULL THEN
                         -- Only attempt lookup if not already populated (e.g. by deletion trigger)
                         IF NEW.route_source IS NULL OR NEW.route_destination IS NULL THEN
                             BEGIN
                                 SELECT r.source, d.city_name INTO NEW.route_source, NEW.route_destination
                                 FROM routes r
                                 JOIN destinations d ON r.destination_code = d.code
                                 WHERE r.id = OLD.route_id;
                             EXCEPTION WHEN OTHERS THEN
                                 -- Ignore
                             END;
                         END IF;
                     END IF;
                 END IF;
                 
                 RETURN NEW;
             END;
             $$ LANGUAGE plpgsql;

             DROP TRIGGER IF EXISTS trg_auto_update_schedule_remarks ON schedules;
             CREATE TRIGGER trg_auto_update_schedule_remarks
             BEFORE INSERT OR UPDATE ON schedules
             FOR EACH ROW EXECUTE FUNCTION sp_auto_update_schedule_remarks();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_auto_update_schedule_remarks ON schedules;
            DROP FUNCTION IF EXISTS sp_auto_update_schedule_remarks();
            DROP FUNCTION IF EXISTS sp_search_trips(TEXT, TEXT, DATE, DECIMAL, DECIMAL);
            DROP PROCEDURE IF EXISTS sp_update_schedule_remarks(TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_create_schedule(TEXT, TEXT, TIMESTAMP, TIMESTAMP, DECIMAL, INT);
            DROP PROCEDURE IF EXISTS sp_check_schedule_conflict(TEXT, TIMESTAMP, TIMESTAMP, BOOLEAN);
        ");
    }
};
