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
            DROP PROCEDURE IF EXISTS sp_create_customer(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_update_customer(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_register_user(TEXT, TEXT, TEXT, TEXT, DATE, TEXT);
            DROP FUNCTION IF EXISTS sp_login_user(TEXT);

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
            END;
            $$;

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
            END;
            $$;

            -- 4.3 DELETE USER (Procedure)
            CREATE OR REPLACE PROCEDURE sp_delete_user(p_id TEXT)
            LANGUAGE plpgsql AS $$
            BEGIN
                DELETE FROM accounts WHERE id = p_id;
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
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS sp_login_user(TEXT);
            DROP PROCEDURE IF EXISTS sp_register_user(TEXT, TEXT, TEXT, TEXT, DATE, TEXT);
            DROP PROCEDURE IF EXISTS sp_update_customer(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, TEXT);
            DROP PROCEDURE IF EXISTS sp_delete_user(TEXT);
            DROP PROCEDURE IF EXISTS sp_create_customer(TEXT, TEXT, TEXT, TEXT, DATE, TEXT, TEXT);
        ");
    }
};
