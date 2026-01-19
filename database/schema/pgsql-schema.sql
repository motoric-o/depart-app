--
-- PostgreSQL database dump
--

\restrict YqHugOq2xYifEjwXyaUofySsU6bVXVHEQupRCBSUBAsjoeOQvF265YrER5pMPQ2

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: backup_deleted_account_name(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.backup_deleted_account_name() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                -- Update Bookings
                UPDATE bookings 
                SET customer_name = OLD.first_name || ' ' || OLD.last_name
                WHERE account_id = OLD.id;

                -- Update Transactions
                UPDATE transactions 
                SET customer_name = OLD.first_name || ' ' || OLD.last_name
                WHERE account_id = OLD.id;

                RETURN OLD;
            END;
            $$;


--
-- Name: generate_custom_id(text, text, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.generate_custom_id(seq_name text, prefix text, padding integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
            DECLARE
                next_val bigint;
            BEGIN
                next_val := nextval(seq_name);
                RETURN prefix || LPAD(next_val::text, padding, '0');
            END;
            $$;


--
-- Name: get_next_date_sequence(text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.get_next_date_sequence(p_key text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
            DECLARE
                v_val INT;
            BEGIN
                INSERT INTO sequence_counters (key, last_value, created_at, updated_at)
                VALUES (p_key, 1, NOW(), NOW())
                ON CONFLICT (key) DO UPDATE 
                SET last_value = sequence_counters.last_value + 1, updated_at = NOW();
                
                SELECT last_value INTO v_val FROM sequence_counters WHERE key = p_key;
                RETURN v_val;
            END;
            $$;


--
-- Name: set_account_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_account_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            DECLARE
                type_name text;
                prefix text;
                period_key text;
                seq_num int;
            BEGIN
                SELECT name INTO type_name FROM account_types WHERE id = NEW.account_type_id;
                
                CASE type_name
                    WHEN 'Financial Admin' THEN prefix := 'FA-';
                    WHEN 'Operations Admin' THEN prefix := 'OA-';
                    WHEN 'Scheduling Admin' THEN prefix := 'SA-';
                    WHEN 'Super Admin' THEN prefix := 'SU-';
                    WHEN 'Owner' THEN prefix := 'OW-';
                    WHEN 'Driver' THEN prefix := 'D-';
                    ELSE prefix := 'C-'; -- Customer
                END CASE;
                
                period_key := to_char(NOW(), 'YYYYMM');
                
                -- Key example: ACC_A_202512
                seq_num := get_next_date_sequence('ACC_' || prefix || period_key);
                
                NEW.id := prefix || period_key || LPAD(seq_num::text, 4, '0');
                RETURN NEW;
            END;
            $$;


--
-- Name: set_booking_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_booking_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            DECLARE
                period_key text;
                seq_num int;
            BEGIN
                IF NEW.id IS NULL THEN
                    period_key := to_char(NOW(), 'YYYY');
                    
                    -- Key example: BK_2025
                    seq_num := get_next_date_sequence('BK_' || period_key);
                    
                    NEW.id := 'BK-' || period_key || '-' || LPAD(seq_num::text, 5, '0');
                END IF;
                RETURN NEW;
            END;
            $$;


--
-- Name: set_bus_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_bus_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                NEW.id := 'BUS' || LPAD(nextval('buses_seq')::text, 3, '0');
                RETURN NEW;
            END;
            $$;


--
-- Name: set_expense_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_expense_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                NEW.id := generate_custom_id('expenses_seq', 'EXP-', 6);
                RETURN NEW;
            END;
            $$;


--
-- Name: set_route_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_route_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                NEW.id := generate_custom_id('routes_seq', 'RTE-', 3);
                RETURN NEW;
            END;
            $$;


--
-- Name: set_schedule_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_schedule_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            DECLARE
                dest_code text;
                period_key text;
                seq_num int;
            BEGIN
                -- We grab the destination code directly from the Route
                SELECT destination_code INTO dest_code FROM routes WHERE id = NEW.route_id;

                period_key := to_char(NEW.departure_time, 'YYMMDD');
                
                -- Key example: SCH_JKT_251213
                seq_num := get_next_date_sequence('SCH_' || dest_code || '_' || period_key);
                
                NEW.id := dest_code || period_key || LPAD(seq_num::text, 3, '0');
                RETURN NEW;
            END;
            $$;


--
-- Name: set_ticket_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_ticket_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            DECLARE
                next_seq int;
                sch_id text;
                b_id text;
            BEGIN
                IF NEW.id IS NULL THEN
                    SELECT b.schedule_id, s.bus_id INTO sch_id, b_id 
                    FROM bookings b JOIN schedules s ON b.schedule_id = s.id 
                    WHERE b.id = NEW.booking_id;
                    
                    -- Count existing tickets for this specific schedule
                    SELECT COALESCE(COUNT(*), 0) + 1 INTO next_seq 
                    FROM tickets t JOIN bookings b ON t.booking_id = b.id 
                    WHERE b.schedule_id = sch_id;
                    
                    NEW.id := sch_id || '-' || b_id || '-' || LPAD(next_seq::text, 2, '0');
                END IF;
                RETURN NEW;
            END;
            $$;


--
-- Name: set_transaction_id(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_transaction_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id := generate_custom_id('transactions_seq', 'TRX-', 6);
                END IF;
                RETURN NEW;
            END;
            $$;


--
-- Name: sp_cancel_booking_atomic(text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_cancel_booking_atomic(IN p_booking_id text)
    LANGUAGE plpgsql
    AS $$
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
                
            END;
            $$;


--
-- Name: sp_cancel_ticket_and_refund(text, numeric); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_cancel_ticket_and_refund(IN p_ticket_id text, IN p_refund_amount numeric)
    LANGUAGE plpgsql
    AS $$
            DECLARE
                v_booking_id TEXT;
                v_account_id TEXT;
            BEGIN
                UPDATE tickets SET status = 'Cancelled', updated_at = NOW() 
                WHERE id = p_ticket_id 
                RETURNING booking_id INTO v_booking_id;

                SELECT account_id INTO v_account_id FROM bookings WHERE id = v_booking_id;

                INSERT INTO transactions (
                    account_id, booking_id, ticket_id, transaction_date, 
                    payment_method, sub_total, total_amount, type, status, created_at, updated_at
                ) VALUES (
                    v_account_id, v_booking_id, p_ticket_id, NOW(), 
                    'System Refund', 0, -p_refund_amount, 'Refund', 'Success', NOW(), NOW()
                );
            END;
            $$;


--
-- Name: sp_check_schedule_conflict(text, timestamp without time zone, timestamp without time zone, boolean); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_check_schedule_conflict(IN p_bus_id text, IN p_departure timestamp without time zone, IN p_arrival timestamp without time zone, INOUT p_conflict boolean)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_check_seat_availability(text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_check_seat_availability(IN p_schedule_id text, IN p_seat_number text)
    LANGUAGE plpgsql
    AS $$
            DECLARE
                v_exists BOOLEAN;
            BEGIN
                SELECT EXISTS(
                    SELECT 1 FROM tickets t 
                    JOIN bookings b ON t.booking_id = b.id 
                    WHERE b.schedule_id = p_schedule_id 
                    AND t.seat_number = p_seat_number
                    AND t.status = 'Valid'
                ) INTO v_exists;

                IF v_exists THEN
                    RAISE EXCEPTION 'Seat % is already booked for Schedule %', p_seat_number, p_schedule_id;
                END IF;
            END;
            $$;


--
-- Name: sp_check_seats_batch(text, text[], boolean); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_check_seats_batch(IN p_schedule_id text, IN p_seat_numbers text[], INOUT p_available boolean)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_create_booking_admin(text, text, text, date, text[], text[], text, text, text, text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_booking_admin(IN p_customer_name text, IN p_customer_email text, IN p_schedule_id text, IN p_travel_date date, IN p_seats text[], IN p_passengers text[], IN p_payment_method text, IN p_payment_status text, IN p_default_password text, IN p_customer_role_id text, INOUT p_booking_id text)
    LANGUAGE plpgsql
    AS $$
            DECLARE
                v_account_id TEXT;
                v_is_available BOOLEAN;
                v_total_price DECIMAL;
                v_seat_price DECIMAL;
                v_seat TEXT;
                v_passenger_name TEXT;
                v_index INT;
                v_txn_status TEXT;
                v_booking_status TEXT;
                v_ticket_status TEXT;
                v_booking_seq TEXT;
                v_ticket_seq INT;
                v_bus_id TEXT;
                v_transaction_id TEXT;
            BEGIN
                -- 1. Check Seats
                CALL sp_check_seats_batch(p_schedule_id, p_seats, v_is_available);
                IF NOT v_is_available THEN
                    RAISE EXCEPTION 'One or more seats are no longer available.';
                END IF;

                -- 2. Find or Create Customer
                SELECT id INTO v_account_id FROM accounts WHERE email = p_customer_email LIMIT 1;
                
                IF v_account_id IS NULL THEN
                    INSERT INTO accounts (id, first_name, last_name, email, password_hash, role, account_type_id, created_at, updated_at)
                    VALUES (
                        generate_ulid(), -- Assuming generate_ulid exists or use uuid_generate_v4()
                        p_customer_name, 
                        '', 
                        p_customer_email, 
                        p_default_password, 
                        'Customer', 
                        p_customer_role_id, 
                        NOW(), 
                        NOW()
                    )
                    RETURNING id INTO v_account_id;
                ELSE
                    -- Update Name if provided?
                    UPDATE accounts SET first_name = p_customer_name WHERE id = v_account_id;
                END IF;

                -- 3. Calculate Price
                SELECT price_per_seat, bus_id INTO v_seat_price, v_bus_id FROM schedules WHERE id = p_schedule_id;
                v_total_price := v_seat_price * array_length(p_seats, 1);

                -- 4. Determine Statuses
                IF p_payment_status = 'Paid' THEN
                    v_booking_status := 'Booked';
                    v_txn_status := 'Success';
                    v_ticket_status := 'Paid'; -- Or 'Valid' based on usage
                ELSE
                    v_booking_status := 'Pending';
                    v_txn_status := 'Pending';
                    v_ticket_status := 'Booked';
                END IF;

                -- 5. Generate Booking ID (BK-YYYY-XXXXX)
                SELECT COUNT(*) + 1 INTO v_booking_seq FROM bookings WHERE EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM NOW());
                p_booking_id := 'BK-' || TO_CHAR(NOW(), 'YYYY') || '-' || LPAD(v_booking_seq::TEXT, 5, '0');

                -- 6. Insert Booking
                INSERT INTO bookings (id, account_id, schedule_id, booking_date, travel_date, status, total_amount, created_at, updated_at)
                VALUES (p_booking_id, v_account_id, p_schedule_id, NOW(), p_travel_date, v_booking_status, v_total_price, NOW(), NOW());

                -- 7. Insert Transaction
                -- Generate ID using standard function if available, or manual ref?
                -- AdminController used: generate_custom_id('transactions_seq', 'TRX-', 6)
                -- We can call it here if it's a function.
                v_transaction_id := generate_custom_id('transactions_seq', 'TRX-', 6);

                INSERT INTO transactions (id, booking_id, account_id, customer_name, sub_total, extra_fees, total_amount, transaction_date, payment_method, type, status, created_at, updated_at)
                VALUES (
                    v_transaction_id,
                    p_booking_id,
                    v_account_id,
                    p_customer_name,
                    v_total_price,
                    0,
                    v_total_price,
                    NOW(),
                    p_payment_method,
                    'Full',
                    v_txn_status,
                    NOW(),
                    NOW()
                );

                -- 8. Insert Tickets
                SELECT COUNT(*) INTO v_ticket_seq FROM tickets 
                JOIN bookings b ON tickets.booking_id = b.id 
                WHERE b.schedule_id = p_schedule_id;

                FOR v_index IN 1 .. array_length(p_seats, 1)
                LOOP
                    v_seat := p_seats[v_index];
                    v_passenger_name := p_passengers[v_index];
                    v_ticket_seq := v_ticket_seq + 1;
                    
                    INSERT INTO tickets (id, booking_id, seat_number, passenger_name, status, transaction_id, created_at, updated_at)
                    VALUES (
                        p_schedule_id || '-' || COALESCE(v_bus_id, 'BUS') || '-' || LPAD(v_ticket_seq::TEXT, 3, '0'),
                        p_booking_id,
                        v_seat,
                        v_passenger_name,
                        v_ticket_status,
                        v_transaction_id,
                        NOW(),
                        NOW()
                    );
                END LOOP;

            END;
            $$;


--
-- Name: sp_create_booking_atomic(text, text, date, text[], numeric, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_booking_atomic(IN p_account_id text, IN p_schedule_id text, IN p_travel_date date, IN p_seat_numbers text[], IN p_total_price numeric, INOUT p_booking_id text)
    LANGUAGE plpgsql
    AS $$
            DECLARE
                v_seat TEXT;
                v_is_available BOOLEAN;
                v_cust_name TEXT;
                v_bus_id TEXT;
                v_booking_id TEXT;
            BEGIN
                -- Check Availability
                CALL sp_check_seats_batch(p_schedule_id, p_seat_numbers, v_is_available);
                
                IF NOT v_is_available THEN
                    RAISE EXCEPTION 'One or more seats are no longer available.';
                END IF;

                -- Create Booking
                INSERT INTO bookings (account_id, schedule_id, booking_date, travel_date, status, total_amount, created_at, updated_at)
                VALUES (p_account_id, p_schedule_id, NOW(), p_travel_date, 'Confirmed', p_total_price, NOW(), NOW())
                RETURNING id INTO v_booking_id;
                
                p_booking_id := v_booking_id;

                -- Get Info for Ticket
                SELECT first_name || ' ' || last_name INTO v_cust_name FROM accounts WHERE id = p_account_id;
                
                -- Create Tickets
                FOREACH v_seat IN ARRAY p_seat_numbers
                LOOP
                     INSERT INTO tickets (booking_id, passenger_name, seat_number, status, created_at, updated_at)
                     VALUES (v_booking_id, v_cust_name, v_seat, 'Confirmed', NOW(), NOW());
                END LOOP;

                -- Create Transaction
                INSERT INTO transactions (id, account_id, booking_id, transaction_date, payment_method, sub_total, total_amount, type, status, created_at, updated_at)
                VALUES (
                    'TRX-' || floor(random() * 1000000)::text,
                    p_account_id, v_booking_id, NOW(), 'Credit Card', p_total_price, p_total_price, 'Payment', 'Success', NOW(), NOW()
                );

                -- Explicit Commit
            END;
            $$;


--
-- Name: sp_create_customer(text, text, text, text, date, text, bigint); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_customer(IN p_first_name text, IN p_last_name text, IN p_email text, IN p_phone text, IN p_birthdate date, IN p_password_hash text, IN p_account_type_id bigint)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO accounts (account_type_id, first_name, last_name, email, phone, birthdate, password_hash, created_at, updated_at)
                VALUES (p_account_type_id, p_first_name, p_last_name, p_email, p_phone, p_birthdate, p_password_hash, NOW(), NOW());
            END;
            $$;


--
-- Name: sp_create_expense(text, numeric, text, date, text, text, text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_expense(IN p_description text, IN p_amount numeric, IN p_type text, IN p_date date, IN p_account_id text, IN p_proof_file text, IN p_transaction_id text, IN p_status text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO expenses (description, amount, type, date, account_id, proof_file, transaction_id, status, created_at, updated_at)
                VALUES (p_description, p_amount, p_type, p_date, p_account_id, p_proof_file, p_transaction_id, p_status, NOW(), NOW());
            END;
            $$;


--
-- Name: sp_create_schedule(text, text, timestamp without time zone, timestamp without time zone, numeric); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_schedule(IN p_route_id text, IN p_bus_id text, IN p_departure_time timestamp without time zone, IN p_arrival_time timestamp without time zone, IN p_price_per_seat numeric)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_create_schedule(text, text, text, timestamp without time zone, timestamp without time zone, numeric, integer); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_schedule(IN p_route_id text, IN p_bus_id text, IN p_driver_id text, IN p_departure_time timestamp without time zone, IN p_arrival_time timestamp without time zone, IN p_price_per_seat numeric, IN p_quota integer)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_create_transaction(text, text, text, text, timestamp without time zone, text, numeric, numeric, numeric, numeric, text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_create_transaction(IN p_account_id text, IN p_customer_name text, IN p_booking_id text, IN p_ticket_id text, IN p_transaction_date timestamp without time zone, IN p_payment_method text, IN p_sub_total numeric, IN p_discount numeric, IN p_extra_fees numeric, IN p_total_amount numeric, IN p_type text, IN p_status text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO transactions (
                    account_id, customer_name, booking_id, ticket_id, transaction_date, 
                    payment_method, sub_total, discount, extra_fees, total_amount, 
                    type, status, created_at, updated_at
                )
                VALUES (
                    p_account_id, p_customer_name, p_booking_id, p_ticket_id, p_transaction_date,
                    p_payment_method, p_sub_total, p_discount, p_extra_fees, p_total_amount,
                    p_type, p_status, NOW(), NOW()
                );
            END;
            $$;


--
-- Name: sp_delete_bus(text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_delete_bus(IN p_id text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                DELETE FROM buses WHERE id = p_id;
            END;
            $$;


--
-- Name: sp_delete_destination(text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_delete_destination(IN p_code text)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_delete_route(text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_delete_route(IN p_id text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                DELETE FROM routes WHERE id = p_id;
            END;
            $$;


--
-- Name: sp_delete_user(text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_delete_user(IN p_id text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                DELETE FROM accounts WHERE id = p_id;
            END;
            $$;


--
-- Name: sp_get_monthly_revenue(integer, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sp_get_monthly_revenue(p_year integer, p_month integer) RETURNS TABLE(report_day date, daily_bookings bigint, daily_revenue numeric)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_get_owner_dashboard_stats(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sp_get_owner_dashboard_stats() RETURNS TABLE(total_bookings bigint, total_revenue numeric, total_customers bigint)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    (SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'),
                    (SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE status = 'Success'),
                    (SELECT COUNT(*) FROM accounts WHERE account_type_id = (SELECT id FROM account_types WHERE name = 'Customer'));
            END;
            $$;


--
-- Name: sp_login_user(text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sp_login_user(p_email text) RETURNS TABLE(id text, password_hash text, first_name text, role text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                RETURN QUERY
                SELECT a.id::TEXT, a.password_hash::TEXT, a.first_name::TEXT, t.name::TEXT
                FROM accounts a
                JOIN account_types t ON a.account_type_id = t.id
                WHERE a.email = p_email;
            END;
            $$;


--
-- Name: sp_manage_bus(text, text, text, text, integer, integer, integer, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_manage_bus(IN p_action text, IN p_id text, IN p_bus_number text, IN p_bus_type text, IN p_capacity integer, IN p_seat_rows integer, IN p_seat_columns integer, IN p_remarks text)
    LANGUAGE plpgsql
    AS $$
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
            END;
            $$;


--
-- Name: sp_manage_bus(text, text, text, text, integer, integer, integer, integer, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_manage_bus(IN p_action text, IN p_id text, IN p_bus_number text, IN p_bus_type text, IN p_capacity integer, IN p_quota integer, IN p_seat_rows integer, IN p_seat_columns integer, IN p_remarks text)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_manage_destination(text, text, text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_manage_destination(IN p_action text, IN p_current_code text, IN p_new_code text, IN p_city_name text)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_manage_route(text, text, text, text, integer, integer); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_manage_route(IN p_action text, IN p_id text, IN p_source text, IN p_destination_code text, IN p_distance integer, IN p_estimated_duration integer)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_manage_schedule_detail(character varying, character varying, character varying, integer, character varying, character varying, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_manage_schedule_detail(IN p_operation character varying, IN p_id character varying, IN p_schedule_id character varying, IN p_sequence integer, IN p_ticket_id character varying, IN p_attendance_status character varying, IN p_remarks text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                IF p_operation = 'CREATE' THEN
                    -- Note: ID generation logic usually in trigger or model. 
                    -- Here we assume caller provides generated ID or DB handles it.
                    -- Recalling model logic: ScheduleDetail has custom ID generation in PHP Model boot().
                    -- For strict SP usage, we might need to duplicate that logic here or pass ID.
                    -- Let's assume we rely on Model for ID if used via Eloquent, 
                    -- BUT if this SP is the *primary* way, we should generate it here.
                    -- However, keeping it simple: Insert into table.
                    
                    INSERT INTO schedule_details (id, schedule_id, sequence, ticket_id, attendance_status, remarks, created_at, updated_at)
                    VALUES (p_schedule_id || '-' || p_sequence, p_schedule_id, p_sequence, p_ticket_id, p_attendance_status, p_remarks, NOW(), NOW());
                    
                ELSIF p_operation = 'UPDATE' THEN
                    UPDATE schedule_details SET 
                        ticket_id = p_ticket_id,
                        attendance_status = p_attendance_status,
                        remarks = p_remarks,
                        updated_at = NOW()
                    WHERE id = p_id;
                    
                ELSIF p_operation = 'DELETE' THEN
                    DELETE FROM schedule_details WHERE id = p_id;
                END IF;
                
                COMMIT;
            END;
            $$;


--
-- Name: sp_manage_schedule_detail(character varying, character varying, character varying, integer, character varying, character varying, character varying, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_manage_schedule_detail(IN p_operation character varying, IN p_id character varying, IN p_schedule_id character varying, IN p_sequence integer, IN p_ticket_id character varying, IN p_seat_number character varying, IN p_attendance_status character varying, IN p_remarks text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                IF p_operation = 'CREATE' THEN
                    -- Note: ID generation logic usually in trigger or model. 
                    -- Here we assume caller provides generated ID or DB handles it.
                    -- Recalling model logic: ScheduleDetail has custom ID generation in PHP Model boot().
                    -- For strict SP usage, we might need to duplicate that logic here or pass ID.
                    -- Let's assume we rely on Model for ID if used via Eloquent, 
                    -- BUT if this SP is the *primary* way, we should generate it here.
                    -- However, keeping it simple: Insert into table.
                    
                    INSERT INTO schedule_details (id, schedule_id, sequence, ticket_id, seat_number, attendance_status, remarks, created_at, updated_at)
                    VALUES (p_schedule_id || '-' || p_sequence, p_schedule_id, p_sequence, p_ticket_id, p_seat_number, p_attendance_status, p_remarks, NOW(), NOW());
                    
                ELSIF p_operation = 'UPDATE' THEN
                    UPDATE schedule_details SET 
                        ticket_id = p_ticket_id,
                        seat_number = p_seat_number,
                        attendance_status = p_attendance_status,
                        remarks = p_remarks,
                        updated_at = NOW()
                    WHERE id = p_id;
                    
                ELSIF p_operation = 'DELETE' THEN
                    DELETE FROM schedule_details WHERE id = p_id;
                END IF;
                
                COMMIT;
            END;
            $$;


--
-- Name: sp_mark_notification_read(text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_mark_notification_read(IN p_id text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE notifications SET read_at = NOW(), updated_at = NOW() WHERE id = p_id;
                COMMIT;
            END;
            $$;


--
-- Name: sp_register_user(text, text, text, text, date, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_register_user(IN p_first_name text, IN p_last_name text, IN p_email text, IN p_phone text, IN p_birthdate date, IN p_password_hash text)
    LANGUAGE plpgsql
    AS $$
            DECLARE
                v_cust_type_id BIGINT;
            BEGIN
                SELECT id INTO v_cust_type_id FROM account_types WHERE name = 'Customer';
                
                INSERT INTO accounts (account_type_id, first_name, last_name, email, phone, birthdate, password_hash, created_at, updated_at)
                VALUES (v_cust_type_id, p_first_name, p_last_name, p_email, p_phone, p_birthdate, p_password_hash, NOW(), NOW());
            END;
            $$;


--
-- Name: sp_search_trips(text, text, date, numeric, numeric); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sp_search_trips(p_source_code text, p_dest_code text, p_date date, p_min_price numeric DEFAULT 0, p_max_price numeric DEFAULT 99999999) RETURNS TABLE(schedule_id text, source text, destination text, departure_time timestamp without time zone, arrival_time timestamp without time zone, price numeric, bus_type text, image_url text)
    LANGUAGE plpgsql
    AS $$
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


--
-- Name: sp_update_booking_status(text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_update_booking_status(IN p_booking_id text, IN p_status text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE bookings SET status = p_status, updated_at = NOW() WHERE id = p_booking_id;
                
                -- Sync logic could go here (e.g. if Cancelled, cancel tickets)
                IF p_status = 'Cancelled' THEN
                    UPDATE tickets SET status = 'Cancelled', updated_at = NOW() WHERE booking_id = p_booking_id;
                END IF;
                
            END;
            $$;


--
-- Name: sp_update_customer(text, text, text, text, text, date, bigint); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_update_customer(IN p_id text, IN p_first_name text, IN p_last_name text, IN p_email text, IN p_phone text, IN p_birthdate date, IN p_account_type_id bigint)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE accounts SET 
                    first_name = p_first_name, last_name = p_last_name, email = p_email, 
                    phone = p_phone, birthdate = p_birthdate, account_type_id = p_account_type_id, updated_at = NOW()
                WHERE id = p_id;
            END;
            $$;


--
-- Name: sp_update_schedule_remarks(text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_update_schedule_remarks(IN p_id text, IN p_remarks text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE schedules SET remarks = p_remarks, updated_at = NOW() WHERE id = p_id;
                COMMIT;
            END;
            $$;


--
-- Name: sp_update_schedule_status(text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_update_schedule_status(IN p_id text, IN p_status text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE schedules SET status = p_status, updated_at = NOW() WHERE id = p_id;
                COMMIT;
            END;
            $$;


--
-- Name: sp_verify_expense(text, text); Type: PROCEDURE; Schema: public; Owner: -
--

CREATE PROCEDURE public.sp_verify_expense(IN p_id text, IN p_status text)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE expenses SET 
                    status = p_status, updated_at = NOW()
                WHERE id = p_id;
            END;
            $$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: account_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.account_types (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: account_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.account_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: account_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.account_types_id_seq OWNED BY public.account_types.id;


--
-- Name: accounts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.accounts (
    id character varying(255) NOT NULL,
    account_type_id bigint NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(255),
    birthdate date,
    password_hash character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: bookings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bookings (
    id character varying(255) NOT NULL,
    account_id character varying(255),
    customer_name character varying(255),
    schedule_id character varying(255) NOT NULL,
    booking_date timestamp(0) without time zone NOT NULL,
    travel_date date NOT NULL,
    total_amount numeric(10,2) NOT NULL,
    status character varying(255) DEFAULT 'Pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: bookmarks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bookmarks (
    id bigint NOT NULL,
    user_id character varying(255) NOT NULL,
    bookmarkable_id character varying(255) NOT NULL,
    bookmarkable_type character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: bookmarks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.bookmarks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: bookmarks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.bookmarks_id_seq OWNED BY public.bookmarks.id;


--
-- Name: buses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.buses (
    id character varying(255) NOT NULL,
    bus_number character varying(255) NOT NULL,
    bus_name character varying(255),
    bus_type character varying(255) NOT NULL,
    capacity integer NOT NULL,
    seat_rows integer NOT NULL,
    seat_columns integer NOT NULL,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: buses_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.buses_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: destinations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.destinations (
    code character varying(5) NOT NULL,
    city_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: expenses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.expenses (
    id character varying(255) NOT NULL,
    description character varying(255) NOT NULL,
    amount numeric(10,2) NOT NULL,
    status character varying(255) DEFAULT 'Approved'::character varying NOT NULL,
    type character varying(255) NOT NULL,
    date date NOT NULL,
    account_id character varying(255),
    proof_file character varying(255),
    transaction_id character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: expenses_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.expenses_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    type character varying(255) NOT NULL,
    notifiable_type character varying(255) NOT NULL,
    notifiable_id bigint NOT NULL,
    data text NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: payment_issue_proofs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.payment_issue_proofs (
    id bigint NOT NULL,
    transaction_id character varying(255) NOT NULL,
    file_path character varying(255),
    message text,
    sender_type character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: payment_issue_proofs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.payment_issue_proofs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: payment_issue_proofs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.payment_issue_proofs_id_seq OWNED BY public.payment_issue_proofs.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_id character varying(255) NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: routes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.routes (
    id character varying(255) NOT NULL,
    source character varying(255) NOT NULL,
    source_code character varying(255),
    destination_code character varying(255) NOT NULL,
    distance integer,
    estimated_duration integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: routes_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.routes_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: schedule_details; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_details (
    id character varying(255) NOT NULL,
    schedule_id character varying(255) NOT NULL,
    sequence integer NOT NULL,
    ticket_id character varying(255),
    seat_number character varying(255),
    attendance_status character varying(255) DEFAULT 'Pending'::character varying NOT NULL,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT schedule_details_attendance_status_check CHECK (((attendance_status)::text = ANY ((ARRAY['Pending'::character varying, 'Present'::character varying, 'Absent'::character varying])::text[])))
);


--
-- Name: schedules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedules (
    id character varying(255) NOT NULL,
    route_id character varying(255),
    route_source character varying(255),
    route_destination character varying(255),
    bus_id character varying(255),
    driver_id character varying(255),
    departure_time timestamp(0) without time zone NOT NULL,
    arrival_time timestamp(0) without time zone NOT NULL,
    price_per_seat numeric(10,2) NOT NULL,
    quota integer NOT NULL,
    remarks text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sequence_counters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sequence_counters (
    key character varying(255) NOT NULL,
    last_value integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id character varying(255),
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: tickets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tickets (
    id character varying(255) NOT NULL,
    booking_id character varying(255) NOT NULL,
    passenger_name character varying(255) NOT NULL,
    seat_number character varying(255) NOT NULL,
    transaction_id character varying(255),
    status character varying(255) DEFAULT 'Valid'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.transactions (
    id character varying(255) NOT NULL,
    account_id character varying(255),
    customer_name character varying(255),
    booking_id character varying(255),
    ticket_id character varying(255),
    transaction_date timestamp(0) without time zone NOT NULL,
    payment_method character varying(255) NOT NULL,
    sub_total numeric(10,2) NOT NULL,
    discount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    extra_fees numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(10,2) NOT NULL,
    type character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'Success'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: transactions_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.transactions_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: view_available_trips; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.view_available_trips AS
 SELECT s.id AS schedule_id,
    r.source,
    r.destination_code,
    d.city_name AS destination_name,
    s.departure_time,
    b.bus_type,
    s.price_per_seat
   FROM (((public.schedules s
     JOIN public.routes r ON (((s.route_id)::text = (r.id)::text)))
     JOIN public.destinations d ON (((r.destination_code)::text = (d.code)::text)))
     JOIN public.buses b ON (((s.bus_id)::text = (b.id)::text)))
  WHERE (s.remarks = 'Scheduled'::text);


--
-- Name: view_customer_bookings; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.view_customer_bookings AS
 SELECT b.id AS booking_id,
    b.account_id,
        CASE
            WHEN (b.account_id IS NOT NULL) THEN (concat(a.first_name, ' ', a.last_name))::character varying
            ELSE b.customer_name
        END AS customer_name,
    a.email,
    b.booking_date,
    b.travel_date,
    b.status AS booking_status,
    b.total_amount,
    r.source AS source_city,
    d.city_name AS destination_city,
    s.departure_time,
    bus.bus_type,
    bus.bus_number,
    ( SELECT count(*) AS count
           FROM public.tickets t
          WHERE ((t.booking_id)::text = (b.id)::text)) AS seat_count
   FROM (((((public.bookings b
     LEFT JOIN public.accounts a ON (((b.account_id)::text = (a.id)::text)))
     JOIN public.schedules s ON (((b.schedule_id)::text = (s.id)::text)))
     JOIN public.routes r ON (((s.route_id)::text = (r.id)::text)))
     JOIN public.destinations d ON (((r.destination_code)::text = (d.code)::text)))
     JOIN public.buses bus ON (((s.bus_id)::text = (bus.id)::text)));


--
-- Name: view_route_stats; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.view_route_stats AS
 SELECT r.id AS route_id,
    concat(r.source_code, ' - ', r.destination_code) AS route_name,
    r.source AS source_name,
    d.city_name AS destination_name,
    r.destination_code,
    count(b.id) AS total_bookings,
    COALESCE(sum(b.total_amount), (0)::numeric) AS total_revenue,
    avg(s.price_per_seat) AS average_ticket_price
   FROM (((public.routes r
     JOIN public.destinations d ON (((r.destination_code)::text = (d.code)::text)))
     LEFT JOIN public.schedules s ON (((r.id)::text = (s.route_id)::text)))
     LEFT JOIN public.bookings b ON ((((s.id)::text = (b.schedule_id)::text) AND ((b.status)::text = 'Confirmed'::text))))
  GROUP BY r.id, r.source, r.source_code, r.destination_code, d.city_name;


--
-- Name: account_types id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.account_types ALTER COLUMN id SET DEFAULT nextval('public.account_types_id_seq'::regclass);


--
-- Name: bookmarks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookmarks ALTER COLUMN id SET DEFAULT nextval('public.bookmarks_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: payment_issue_proofs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_issue_proofs ALTER COLUMN id SET DEFAULT nextval('public.payment_issue_proofs_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: account_types account_types_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.account_types
    ADD CONSTRAINT account_types_name_unique UNIQUE (name);


--
-- Name: account_types account_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.account_types
    ADD CONSTRAINT account_types_pkey PRIMARY KEY (id);


--
-- Name: accounts accounts_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_email_unique UNIQUE (email);


--
-- Name: accounts accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_pkey PRIMARY KEY (id);


--
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- Name: bookmarks bookmarks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookmarks
    ADD CONSTRAINT bookmarks_pkey PRIMARY KEY (id);


--
-- Name: buses buses_bus_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buses
    ADD CONSTRAINT buses_bus_number_unique UNIQUE (bus_number);


--
-- Name: buses buses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.buses
    ADD CONSTRAINT buses_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: destinations destinations_city_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.destinations
    ADD CONSTRAINT destinations_city_name_unique UNIQUE (city_name);


--
-- Name: destinations destinations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.destinations
    ADD CONSTRAINT destinations_pkey PRIMARY KEY (code);


--
-- Name: expenses expenses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payment_issue_proofs payment_issue_proofs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_issue_proofs
    ADD CONSTRAINT payment_issue_proofs_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: routes routes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_pkey PRIMARY KEY (id);


--
-- Name: schedule_details schedule_details_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_details
    ADD CONSTRAINT schedule_details_pkey PRIMARY KEY (id);


--
-- Name: schedules schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_pkey PRIMARY KEY (id);


--
-- Name: sequence_counters sequence_counters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sequence_counters
    ADD CONSTRAINT sequence_counters_pkey PRIMARY KEY (key);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tickets tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_pkey PRIMARY KEY (id);


--
-- Name: transactions transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- Name: bookmarks_bookmarkable_id_bookmarkable_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX bookmarks_bookmarkable_id_bookmarkable_type_index ON public.bookmarks USING btree (bookmarkable_id, bookmarkable_type);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: notifications_notifiable_type_notifiable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notifications_notifiable_type_notifiable_id_index ON public.notifications USING btree (notifiable_type, notifiable_id);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: accounts trg_backup_account_name; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_backup_account_name BEFORE DELETE ON public.accounts FOR EACH ROW EXECUTE FUNCTION public.backup_deleted_account_name();


--
-- Name: accounts trg_set_account_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_account_id BEFORE INSERT ON public.accounts FOR EACH ROW EXECUTE FUNCTION public.set_account_id();


--
-- Name: bookings trg_set_booking_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_booking_id BEFORE INSERT ON public.bookings FOR EACH ROW EXECUTE FUNCTION public.set_booking_id();


--
-- Name: buses trg_set_bus_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_bus_id BEFORE INSERT ON public.buses FOR EACH ROW EXECUTE FUNCTION public.set_bus_id();


--
-- Name: expenses trg_set_expense_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_expense_id BEFORE INSERT ON public.expenses FOR EACH ROW EXECUTE FUNCTION public.set_expense_id();


--
-- Name: routes trg_set_route_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_route_id BEFORE INSERT ON public.routes FOR EACH ROW EXECUTE FUNCTION public.set_route_id();


--
-- Name: schedules trg_set_schedule_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_schedule_id BEFORE INSERT ON public.schedules FOR EACH ROW EXECUTE FUNCTION public.set_schedule_id();


--
-- Name: tickets trg_set_ticket_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_ticket_id BEFORE INSERT ON public.tickets FOR EACH ROW EXECUTE FUNCTION public.set_ticket_id();


--
-- Name: transactions trg_set_transaction_id; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_set_transaction_id BEFORE INSERT ON public.transactions FOR EACH ROW EXECUTE FUNCTION public.set_transaction_id();


--
-- Name: accounts accounts_account_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_account_type_id_foreign FOREIGN KEY (account_type_id) REFERENCES public.account_types(id);


--
-- Name: bookings bookings_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: bookings bookings_schedule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES public.schedules(id);


--
-- Name: bookmarks bookmarks_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bookmarks
    ADD CONSTRAINT bookmarks_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


--
-- Name: expenses expenses_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: expenses expenses_transaction_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_transaction_id_foreign FOREIGN KEY (transaction_id) REFERENCES public.transactions(id) ON DELETE SET NULL;


--
-- Name: payment_issue_proofs payment_issue_proofs_transaction_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_issue_proofs
    ADD CONSTRAINT payment_issue_proofs_transaction_id_foreign FOREIGN KEY (transaction_id) REFERENCES public.transactions(id) ON DELETE CASCADE;


--
-- Name: routes routes_destination_code_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_destination_code_foreign FOREIGN KEY (destination_code) REFERENCES public.destinations(code) ON UPDATE CASCADE;


--
-- Name: routes routes_source_code_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_source_code_foreign FOREIGN KEY (source_code) REFERENCES public.destinations(code) ON UPDATE CASCADE;


--
-- Name: schedule_details schedule_details_schedule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_details
    ADD CONSTRAINT schedule_details_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES public.schedules(id) ON DELETE CASCADE;


--
-- Name: schedule_details schedule_details_ticket_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_details
    ADD CONSTRAINT schedule_details_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES public.tickets(id) ON DELETE SET NULL;


--
-- Name: schedules schedules_bus_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_bus_id_foreign FOREIGN KEY (bus_id) REFERENCES public.buses(id) ON DELETE SET NULL;


--
-- Name: schedules schedules_driver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: schedules schedules_route_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_route_id_foreign FOREIGN KEY (route_id) REFERENCES public.routes(id) ON DELETE SET NULL;


--
-- Name: tickets tickets_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id);


--
-- Name: tickets tickets_transaction_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_transaction_id_foreign FOREIGN KEY (transaction_id) REFERENCES public.transactions(id) ON DELETE SET NULL;


--
-- Name: transactions transactions_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: transactions transactions_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.bookings(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict YqHugOq2xYifEjwXyaUofySsU6bVXVHEQupRCBSUBAsjoeOQvF265YrER5pMPQ2

--
-- PostgreSQL database dump
--

\restrict wNNcpfgPBiJ0duyKiQVKImFasKnc6IlNbi3fp99djrXhUAczaGrXuwdhrisZ4wK

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0000_00_00_000001_create_cache_table	1
2	2025_12_13_000001_setup_custom_id_logic	1
3	2025_12_13_000002_create_account_types_table	1
4	2025_12_13_000003_create_destinations_table	1
5	2025_12_13_000004_create_buses_table	1
6	2025_12_13_000005_create_routes_table	1
7	2025_12_13_000006_create_accounts_table	1
8	2025_12_13_000007_create_schedules_table	1
9	2025_12_13_000008_create_bookings_table	1
10	2025_12_13_000009_create_transactions_table	1
11	2025_12_13_000010_create_tickets_table	1
12	2025_12_13_000011_create_stored_procedures	1
13	2025_12_13_000012_create_database_views	1
14	2025_12_13_000013_create_notifications_table	1
15	2025_12_14_045429_create_personal_access_tokens_table	1
16	2025_12_22_043004_create_sessions_table	1
17	2026_01_01_183047_create_password_reset_tokens_table	1
18	2026_01_14_000001_create_expenses_table	1
19	2026_01_14_000001_create_schedule_details_table	1
20	2026_01_14_000002_create_bus_procedures	1
21	2026_01_14_000003_create_route_procedures	1
22	2026_01_14_000004_create_schedule_procedures	1
23	2026_01_14_000005_create_customer_procedures	1
24	2026_01_14_000006_create_booking_procedures	1
25	2026_01_14_000007_create_reporting_procedures	1
26	2026_01_14_000008_create_schedule_detail_procedures	1
27	2026_01_17_000001_create_payment_issue_proofs_table	1
28	2026_01_17_133820_create_bookmarks_table	1
29	2026_01_18_190000_create_expense_procedures	1
30	2026_01_18_193000_create_transaction_procedures	1
31	2026_01_18_203000_create_destination_procedures	1
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 31, true);


--
-- PostgreSQL database dump complete
--

\unrestrict wNNcpfgPBiJ0duyKiQVKImFasKnc6IlNbi3fp99djrXhUAczaGrXuwdhrisZ4wK

