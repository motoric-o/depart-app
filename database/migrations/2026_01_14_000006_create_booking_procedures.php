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
            DROP FUNCTION IF EXISTS sp_check_seats_batch(TEXT, TEXT[]);
            DROP PROCEDURE IF EXISTS sp_check_seats_batch(TEXT, TEXT[], BOOLEAN);
            DROP FUNCTION IF EXISTS sp_create_booking_atomic(TEXT, TEXT, DATE, TEXT[], DECIMAL);
            DROP PROCEDURE IF EXISTS sp_create_booking_atomic(TEXT, TEXT, DATE, TEXT[], DECIMAL, TEXT);
            DROP PROCEDURE IF EXISTS sp_cancel_booking_atomic(TEXT);

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
                
            END;
            $$;

            -- 2.3 CREATE BOOKING ADMIN (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_booking_admin(
                p_customer_name TEXT,
                p_customer_email TEXT,
                p_schedule_id TEXT,
                p_travel_date DATE,
                p_seats TEXT[], -- Array of seat numbers
                p_passengers TEXT[], -- Array of passenger names, indexed parallel to seats
                p_payment_method TEXT,
                p_payment_status TEXT, -- 'Paid', 'Pending', etc.
                p_default_password TEXT, -- For new user creation
                p_customer_role_id TEXT,
                INOUT p_booking_id TEXT
            )
            LANGUAGE plpgsql AS $$
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

            -- 2.4 UPDATE BOOKING STATUS (Procedure)
            CREATE OR REPLACE PROCEDURE sp_update_booking_status(
                p_booking_id TEXT,
                p_status TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE bookings SET status = p_status, updated_at = NOW() WHERE id = p_booking_id;
                
                -- Sync logic could go here (e.g. if Cancelled, cancel tickets)
                IF p_status = 'Cancelled' THEN
                    UPDATE tickets SET status = 'Cancelled', updated_at = NOW() WHERE booking_id = p_booking_id;
                END IF;
                
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
            DROP PROCEDURE IF EXISTS sp_cancel_booking_atomic(TEXT);
            DROP PROCEDURE IF EXISTS sp_create_booking_atomic(TEXT, TEXT, DATE, TEXT[], DECIMAL, TEXT);
            DROP PROCEDURE IF EXISTS sp_check_seats_batch(TEXT, TEXT[], BOOLEAN);
        ");
    }
};
