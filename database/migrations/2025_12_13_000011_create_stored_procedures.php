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
            -- 1. BATCH SEAT AVAILABILITY CHECK
            CREATE OR REPLACE FUNCTION sp_check_seats_batch(p_schedule_id TEXT, p_seat_numbers TEXT[])
            RETURNS BOOLEAN LANGUAGE plpgsql AS $$
            DECLARE
                v_count INT;
            BEGIN
                SELECT COUNT(*) INTO v_count
                FROM tickets t 
                JOIN bookings b ON t.booking_id = b.id 
                WHERE b.schedule_id = p_schedule_id 
                AND t.seat_number = ANY(p_seat_numbers)
                AND (t.status = 'Valid' OR t.status = 'Confirmed');
                
                RETURN v_count = 0;
            END;
            $$;

            -- 2. ATOMIC BOOKING CREATION
            CREATE OR REPLACE FUNCTION sp_create_booking_atomic(
                p_account_id TEXT, 
                p_schedule_id TEXT, 
                p_travel_date DATE, 
                p_seat_numbers TEXT[], 
                p_total_price DECIMAL
            )
            RETURNS TEXT LANGUAGE plpgsql AS $$
            DECLARE
                v_booking_id TEXT;
                v_seat TEXT;
                v_is_available BOOLEAN;
                v_schedule_price DECIMAL;
                v_ticket_id TEXT;
                v_seq_num INT;
                v_period_key TEXT;
                v_cust_name TEXT;
                v_bus_id TEXT;
            BEGIN
                -- Double Check Availability (Concurrency Safety)
                IF NOT sp_check_seats_batch(p_schedule_id, p_seat_numbers) THEN
                    RAISE EXCEPTION 'One or more seats are no longer available.';
                END IF;

                -- Generate Booking ID (Reusing logic from trigger or doing it manually here since we are bypassing model events might be safer to let trigger handle it? 
                -- Actually, if we insert into table, trigger fires. Let's rely on the Trigger for ID generation if possible, 
                -- BUT RETURNING clause is best.
                
                INSERT INTO bookings (account_id, schedule_id, booking_date, travel_date, status, total_amount, created_at, updated_at)
                VALUES (p_account_id, p_schedule_id, NOW(), p_travel_date, 'Confirmed', p_total_price, NOW(), NOW())
                RETURNING id INTO v_booking_id;

                -- Get Customer Name for Ticket
                SELECT first_name || ' ' || last_name INTO v_cust_name FROM accounts WHERE id = p_account_id;
                
                -- Get Bus ID for Ticket ID generation
                SELECT bus_id INTO v_bus_id FROM schedules WHERE id = p_schedule_id;

                -- Create Tickets
                FOREACH v_seat IN ARRAY p_seat_numbers
                LOOP
                     -- Manual ID generation to be fast: SCHID-BUSID-SEAT (Simple custom format) or let trigger do it.
                     -- Let's rely on the Ticket Trigger for ID generation to be consistent with codebase.
                     INSERT INTO tickets (booking_id, passenger_name, seat_number, status, created_at, updated_at)
                     VALUES (v_booking_id, v_cust_name, v_seat, 'Confirmed', NOW(), NOW());
                END LOOP;

                -- Create Transaction Record (Pending Payment or Direct Success? Assuming Success for now as per `store` logic)
                INSERT INTO transactions (id, account_id, booking_id, transaction_date, payment_method, sub_total, total_amount, type, status, created_at, updated_at)
                VALUES (
                    'TRX-' || floor(random() * 1000000)::text, -- Simple ID for now, ideally use sequence
                    p_account_id, v_booking_id, NOW(), 'Credit Card', p_total_price, p_total_price, 'Payment', 'Success', NOW(), NOW()
                );

                RETURN v_booking_id;
            END;
            $$;

            -- 3. ATOMIC BOOKING CANCELLATION
            CREATE OR REPLACE PROCEDURE sp_cancel_booking_atomic(p_booking_id TEXT)
            LANGUAGE plpgsql AS $$
            DECLARE
                v_total_amount DECIMAL;
                v_account_id TEXT;
            BEGIN
                SELECT total_amount, account_id INTO v_total_amount, v_account_id FROM bookings WHERE id = p_booking_id;
                
                -- Update Booking
                UPDATE bookings SET status = 'Cancelled', updated_at = NOW() WHERE id = p_booking_id;
                
                -- Update Tickets
                UPDATE tickets SET status = 'Cancelled', updated_at = NOW() WHERE booking_id = p_booking_id;

                -- Record Refund Transaction
                INSERT INTO transactions (id, account_id, booking_id, transaction_date, payment_method, sub_total, total_amount, type, status, created_at, updated_at)
                VALUES (
                     'REF-' || floor(random() * 1000000)::text,
                     v_account_id, p_booking_id, NOW(), 'System', 0, -v_total_amount, 'Refund', 'Success', NOW(), NOW()
                );
            END;
            $$;

            -- 4. MONTHLY REVENUE REPORT
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
                AND type = 'Payment'  -- Exclude refunds from positive revenue? Or Net? Let's use Payment type for Gross.
                GROUP BY DATE(transaction_date)
                ORDER BY report_day;
            END;
            $$;

            -- 5. OWNER DASHBOARD STATS
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

            -- 6. SEARCH TRIPS
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
                    s.id,
                    r.source,
                    d.city_name,
                    s.departure_time,
                    s.arrival_time,
                    s.price_per_seat,
                    b.bus_type,
                    b.bus_number -- Using bus number as image placeholder or join w/ another table
                FROM schedules s
                JOIN routes r ON s.route_id = r.id
                JOIN destinations d ON r.destination_code = d.code
                JOIN buses b ON s.bus_id = b.id
                WHERE (r.source_code = p_source_code OR r.source LIKE '%' || p_source_code || '%')
                AND r.destination_code = p_dest_code
                AND s.status = 'Scheduled'
                AND DATE(s.departure_time) >= p_date
                AND s.price_per_seat BETWEEN p_min_price AND p_max_price
                ORDER BY s.departure_time ASC;
            END;
            $$;

            -- 7. SCHEDULE CONFLICT CHECK
            CREATE OR REPLACE FUNCTION sp_check_schedule_conflict(
                p_bus_id TEXT, 
                p_departure TIMESTAMP, 
                p_arrival TIMESTAMP
            )
            RETURNS BOOLEAN
            LANGUAGE plpgsql AS $$
            DECLARE
                v_conflict INT;
            BEGIN
                SELECT COUNT(*) INTO v_conflict
                FROM schedules
                WHERE bus_id = p_bus_id
                AND status != 'Cancelled'
                AND (
                    (departure_time <= p_departure AND arrival_time >= p_departure) OR
                    (departure_time <= p_arrival AND arrival_time >= p_arrival) OR
                    (departure_time >= p_departure AND arrival_time <= p_arrival)
                );
                
                RETURN v_conflict > 0;
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
