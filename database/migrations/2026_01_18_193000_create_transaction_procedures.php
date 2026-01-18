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
            DROP PROCEDURE IF EXISTS sp_create_transaction(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, NUMERIC, NUMERIC, NUMERIC, NUMERIC, TEXT, TEXT);

            -- CREATE TRANSACTION (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_transaction(
                p_account_id TEXT,
                p_customer_name TEXT,
                p_booking_id TEXT,
                p_ticket_id TEXT, -- Optional, often linked later or for specific ticket purchase
                p_transaction_date TIMESTAMP,
                p_payment_method TEXT,
                p_sub_total NUMERIC,
                p_discount NUMERIC,
                p_extra_fees NUMERIC,
                p_total_amount NUMERIC,
                p_type TEXT,
                p_status TEXT
            )
            LANGUAGE plpgsql AS $$
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
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_create_transaction(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, NUMERIC, NUMERIC, NUMERIC, NUMERIC, TEXT, TEXT);
        ");
    }
};
