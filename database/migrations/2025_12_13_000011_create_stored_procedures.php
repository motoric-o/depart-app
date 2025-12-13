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
            -- 1. ATOMIC BOOKING CHECK
            CREATE OR REPLACE PROCEDURE sp_check_seat_availability(p_schedule_id TEXT, p_seat_number TEXT)
            LANGUAGE plpgsql AS $$
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

            -- 2. TICKET CANCELLATION & REFUND
            CREATE OR REPLACE PROCEDURE sp_cancel_ticket_and_refund(
                p_ticket_id TEXT,
                p_refund_amount DECIMAL
            )
            LANGUAGE plpgsql AS $$
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
