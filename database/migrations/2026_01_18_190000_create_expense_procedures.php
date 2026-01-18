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
            DROP PROCEDURE IF EXISTS sp_create_expense(TEXT, NUMERIC, TEXT, DATE, TEXT, TEXT, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_verify_expense(TEXT, TEXT);

            -- CREATE EXPENSE (Procedure)
            CREATE OR REPLACE PROCEDURE sp_create_expense(
                p_description TEXT,
                p_amount NUMERIC,
                p_type TEXT,
                p_date DATE,
                p_account_id TEXT,
                p_proof_file TEXT,
                p_transaction_id TEXT,
                p_status TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                INSERT INTO expenses (description, amount, type, date, account_id, proof_file, transaction_id, status, created_at, updated_at)
                VALUES (p_description, p_amount, p_type, p_date, p_account_id, p_proof_file, p_transaction_id, p_status, NOW(), NOW());
            END;
            $$;

            -- VERIFY EXPENSE (Procedure)
            CREATE OR REPLACE PROCEDURE sp_verify_expense(
                p_id TEXT,
                p_status TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                UPDATE expenses SET 
                    status = p_status, updated_at = NOW()
                WHERE id = p_id;
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
            DROP PROCEDURE IF EXISTS sp_verify_expense(TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_create_expense(TEXT, NUMERIC, TEXT, DATE, TEXT, TEXT, TEXT, TEXT);
        ");
    }
};
