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
