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
        DB::unprepared("
            -- Clean up potential conflicts (Functions vs Procedures)
            DROP FUNCTION IF EXISTS sp_check_seats_batch(TEXT, TEXT[]);
            DROP PROCEDURE IF EXISTS sp_check_seats_batch(TEXT, TEXT[], BOOLEAN);
            
            DROP FUNCTION IF EXISTS sp_create_booking_atomic(TEXT, TEXT, DATE, TEXT[], DECIMAL);
            DROP PROCEDURE IF EXISTS sp_create_booking_atomic(TEXT, TEXT, DATE, TEXT[], DECIMAL, TEXT);
            
            DROP FUNCTION IF EXISTS sp_check_schedule_conflict(TEXT, TIMESTAMP, TIMESTAMP);
            DROP PROCEDURE IF EXISTS sp_check_schedule_conflict(TEXT, TIMESTAMP, TIMESTAMP, BOOLEAN);

            DROP PROCEDURE IF EXISTS sp_cancel_booking_atomic(TEXT);
            DROP PROCEDURE IF EXISTS sp_manage_bus(TEXT, TEXT, TEXT, TEXT, INT, INT, INT, INT, TEXT);
            DROP PROCEDURE IF EXISTS sp_manage_route(TEXT, TEXT, TEXT, TEXT, INT, INT);
            DROP PROCEDURE IF EXISTS sp_create_schedule(TEXT, TEXT, TIMESTAMP, TIMESTAMP, DECIMAL);
            DROP PROCEDURE IF EXISTS sp_update_schedule_status(TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_create_customer(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_update_customer(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_register_user(TEXT, TEXT, TEXT, TEXT, DATE, TEXT);
            DROP FUNCTION IF EXISTS sp_login_user(TEXT);
            DROP PROCEDURE IF EXISTS sp_mark_notification_read(TEXT);
            
            DROP FUNCTION IF EXISTS sp_get_monthly_revenue(INT, INT);
            DROP FUNCTION IF EXISTS sp_get_owner_dashboard_stats();
            DROP FUNCTION IF EXISTS sp_search_trips(TEXT, TEXT, DATE, DECIMAL, DECIMAL);

            -- =============================================
            -- 1. UTILITY / VALIDATION PROCEDURES
            -- =============================================

            -- 1.1 CHECK SEATS (Procedure)
            CREATE OR REPLACE PROCEDURE sp_check_seats_batch(
                p_schedule_id TEXT, 
                p_seat_numbers TEXT[], 
                INOUT p_available BOOLEAN
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_count INT;
            BEGIN
                SELECT COUNT(*) INTO v_count
                FROM tickets t 
                JOIN bookings b ON t.booking_id = b.id 
                WHERE b.schedule_id = p_schedule_id 
                AND t.seat_number = ANY(p_seat_numbers)
                AND (t.status = 'Valid' OR t.status = 'Confirmed');
                
                p_available := (v_count = 0);
            END;
            $$;

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
                AND status != 'Cancelled'
                AND (
                    (departure_time <= p_departure AND arrival_time >= p_departure) OR
                    (departure_time <= p_arrival AND arrival_time >= p_arrival) OR
                    (departure_time >= p_departure AND arrival_time <= p_arrival)
                );
                
                p_conflict := (v_count > 0);
            END;
            $$;


            -- =============================================
            -- 2. BOOKING TRANSACTIONS
            -- =============================================

            -- 2.1 CREATE BOOKING (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_booking_atomic(
                p_account_id TEXT, 
                p_schedule_id TEXT, 
                p_travel_date DATE, 
                p_seat_numbers TEXT[], 
                p_total_price DECIMAL,
                INOUT p_booking_id TEXT
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_seat TEXT;
                v_is_available BOOLEAN;
                v_cust_name TEXT;
                v_bus_id TEXT;
            BEGIN
                -- Check Availability
                CALL sp_check_seats_batch(p_schedule_id, p_seat_numbers, v_is_available);
                
                IF NOT v_is_available THEN
                    RAISE EXCEPTION 'One or more seats are no longer available.';
                END IF;

                -- Create Booking
                INSERT INTO bookings (account_id, schedule_id, booking_date, travel_date, status, total_amount, created_at, updated_at)
                VALUES (p_account_id, p_schedule_id, NOW(), p_travel_date, 'Confirmed', p_total_price, NOW(), NOW())
                RETURNING id INTO p_booking_id;

                -- Get Info for Ticket
                SELECT first_name || ' ' || last_name INTO v_cust_name FROM accounts WHERE id = p_account_id;
                
                -- Create Tickets
                FOREACH v_seat IN ARRAY p_seat_numbers
                LOOP
                     INSERT INTO tickets (booking_id, passenger_name, seat_number, status, created_at, updated_at)
                     VALUES (p_booking_id, v_cust_name, v_seat, 'Confirmed', NOW(), NOW());
                END LOOP;

                -- Create Transaction
                INSERT INTO transactions (id, account_id, booking_id, transaction_date, payment_method, sub_total, total_amount, type, status, created_at, updated_at)
                VALUES (
                    'TRX-' || floor(random() * 1000000)::text,
                    p_account_id, p_booking_id, NOW(), 'Credit Card', p_total_price, p_total_price, 'Payment', 'Success', NOW(), NOW()
                );

                -- Explicit Commit
                COMMIT;
            END;
            $$;

            -- 2.2 CANCEL BOOKING (Procedure)
            CREATE OR REPLACE PROCEDURE sp_cancel_booking_atomic(p_booking_id TEXT)
            LANGUAGE plpgsql AS $$
            DECLARE
                v_total_amount DECIMAL;
                v_account_id TEXT;
            BEGIN
                SELECT total_amount, account_id INTO v_total_amount, v_account_id FROM bookings WHERE id = p_booking_id;
                
                UPDATE bookings SET status = 'Cancelled', updated_at = NOW() WHERE id = p_booking_id;
                UPDATE tickets SET status = 'Cancelled', updated_at = NOW() WHERE booking_id = p_booking_id;

                INSERT INTO transactions (id, account_id, booking_id, transaction_date, payment_method, sub_total, total_amount, type, status, created_at, updated_at)
                VALUES (
                     'REF-' || floor(random() * 1000000)::text,
                     v_account_id, p_booking_id, NOW(), 'System', 0, -v_total_amount, 'Refund', 'Success', NOW(), NOW()
                );
                
                COMMIT;
            END;
            $$;


            -- =============================================
            -- 3. ADMIN / MANAGEMENT PROCEDURES
            -- =============================================

            -- 3.1 MANAGE BUS (Procedure)
            CREATE OR REPLACE PROCEDURE sp_manage_bus(
                p_action TEXT, -- 'CREATE' or 'UPDATE'
                p_id TEXT, -- Null for CREATE
                p_bus_number TEXT,
                p_bus_type TEXT,
                p_capacity INT,
                p_quota INT,
                p_seat_rows INT,
                p_seat_columns INT,
                p_remarks TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                IF p_action = 'CREATE' THEN
                    INSERT INTO buses (bus_number, bus_type, capacity, quota, seat_rows, seat_columns, remarks, created_at, updated_at)
                    VALUES (p_bus_number, p_bus_type, p_capacity, p_quota, p_seat_rows, p_seat_columns, p_remarks, NOW(), NOW());
                ELSIF p_action = 'UPDATE' THEN
                    UPDATE buses SET 
                        bus_number = p_bus_number, bus_type = p_bus_type, capacity = p_capacity, 
                        quota = p_quota, seat_rows = p_seat_rows, seat_columns = p_seat_columns, 
                        remarks = p_remarks, updated_at = NOW()
                    WHERE id = p_id;
                END IF;
                COMMIT;
            END;
            $$;

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
                -- Naive generation removed to prevent FK violation. Defaulting to NULL.
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

            -- 3.3 CREATE SCHEDULE (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_schedule(
                p_route_id TEXT,
                p_bus_id TEXT,
                p_departure_time TIMESTAMP,
                p_arrival_time TIMESTAMP,
                p_price_per_seat DECIMAL
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_conflict BOOLEAN;
            BEGIN
                CALL sp_check_schedule_conflict(p_bus_id, p_departure_time, p_arrival_time, v_conflict);
                
                IF v_conflict THEN
                    RAISE EXCEPTION 'Bus is already scheduled for this time range.';
                END IF;

                INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, price_per_seat, status, created_at, updated_at)
                VALUES (p_route_id, p_bus_id, p_departure_time, p_arrival_time, p_price_per_seat, 'Scheduled', NOW(), NOW());
                
                COMMIT;
            END;
            $$;

            -- 3.4 UPDATE SCHEDULE STATUS (Procedure)
            CREATE OR REPLACE PROCEDURE sp_update_schedule_status(p_id TEXT, p_status TEXT)
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE schedules SET status = p_status, updated_at = NOW() WHERE id = p_id;
                COMMIT;
            END;
            $$;

            -- 3.5 CREATE CUSTOMER (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_customer(
                p_first_name TEXT, p_last_name TEXT, p_email TEXT, 
                p_phone TEXT, p_birthdate DATE, p_password_hash TEXT,
                p_account_type_id BIGINT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                INSERT INTO accounts (account_type_id, first_name, last_name, email, phone, birthdate, password_hash, created_at, updated_at)
                VALUES (p_account_type_id, p_first_name, p_last_name, p_email, p_phone, p_birthdate, p_password_hash, NOW(), NOW());
                COMMIT;
            END;
            $$;

            -- 3.6 UPDATE CUSTOMER (Procedure)
            CREATE OR REPLACE PROCEDURE sp_update_customer(
                p_id TEXT, p_first_name TEXT, p_last_name TEXT, 
                p_email TEXT, p_phone TEXT, p_birthdate DATE,
                p_account_type_id BIGINT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE accounts SET 
                    first_name = p_first_name, last_name = p_last_name, email = p_email, 
                    phone = p_phone, birthdate = p_birthdate, account_type_id = p_account_type_id, updated_at = NOW()
                WHERE id = p_id;
                COMMIT;
            END;
            $$;


            -- =============================================
            -- 4. AUTH & NOTIFICATIONS
            -- =============================================

            -- 4.1 REGISTER USER (Procedure)
            CREATE OR REPLACE PROCEDURE sp_register_user(
                p_first_name TEXT, p_last_name TEXT, p_email TEXT, 
                p_phone TEXT, p_birthdate DATE, p_password_hash TEXT
            )
            LANGUAGE plpgsql AS $$
            DECLARE
                v_cust_type_id BIGINT;
            BEGIN
                SELECT id INTO v_cust_type_id FROM account_types WHERE name = 'Customer';
                
                INSERT INTO accounts (account_type_id, first_name, last_name, email, phone, birthdate, password_hash, created_at, updated_at)
                VALUES (v_cust_type_id, p_first_name, p_last_name, p_email, p_phone, p_birthdate, p_password_hash, NOW(), NOW());
                COMMIT;
            END;
            $$;

            -- 4.2 LOGIN USER HELP (Function)
            CREATE OR REPLACE FUNCTION sp_login_user(p_email TEXT)
            RETURNS TABLE(id TEXT, password_hash TEXT, first_name TEXT, role TEXT)
            LANGUAGE plpgsql AS $$
            BEGIN
                RETURN QUERY
                SELECT a.id::TEXT, a.password_hash::TEXT, a.first_name::TEXT, t.name::TEXT
                FROM accounts a
                JOIN account_types t ON a.account_type_id = t.id
                WHERE a.email = p_email;
            END;
            $$;

            -- 4.3 MARK NOTIFICATION READ (Procedure)
            CREATE OR REPLACE PROCEDURE sp_mark_notification_read(p_id TEXT)
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE notifications SET read_at = NOW(), updated_at = NOW() WHERE id = p_id;
                COMMIT;
            END;
            $$;


            -- =============================================
            -- 5. READ-ONLY FUNCTIONS (Reporting / Search)
            -- =============================================
            
            -- 5.1 MONTHLY REVENUE (Function)
            CREATE OR REPLACE FUNCTION sp_get_monthly_revenue(p_year INT, p_month INT)
            RETURNS TABLE (
                report_day DATE,
                daily_bookings BIGINT,
                daily_revenue DECIMAL
            ) 
            LANGUAGE plpgsql AS $$
            BEGIN
                RETURN QUERY
                SELECT 
                    DATE(transaction_date) as report_day,
                    COUNT(DISTINCT booking_id) as daily_bookings,
                    SUM(total_amount) as daily_revenue
                FROM transactions
                WHERE EXTRACT(YEAR FROM transaction_date) = p_year
                AND EXTRACT(MONTH FROM transaction_date) = p_month
                AND status = 'Success'
                AND type = 'Payment'
                GROUP BY DATE(transaction_date)
                ORDER BY report_day;
            END;
            $$;

            -- 5.2 OWNER DASHBOARD STATS (Function)
            CREATE OR REPLACE FUNCTION sp_get_owner_dashboard_stats()
            RETURNS TABLE (
                total_bookings BIGINT,
                total_revenue DECIMAL,
                total_customers BIGINT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    (SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'),
                    (SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE status = 'Success'),
                    (SELECT COUNT(*) FROM accounts WHERE account_type_id = (SELECT id FROM account_types WHERE name = 'Customer'));
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
                AND s.status = 'Scheduled'
                AND DATE(s.departure_time) >= p_date
                AND s.price_per_seat BETWEEN p_min_price AND p_max_price
                ORDER BY s.departure_time ASC;
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
            DROP PROCEDURE IF EXISTS sp_check_seat_availability(TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_cancel_ticket_and_refund(TEXT, DECIMAL);
        ");
    }
};
