<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION backup_deleted_account_name() RETURNS TRIGGER AS $$
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
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_backup_account_name 
            BEFORE DELETE ON accounts 
            FOR EACH ROW 
            EXECUTE FUNCTION backup_deleted_account_name();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_backup_account_name ON accounts;
            DROP FUNCTION IF EXISTS backup_deleted_account_name();
        ");
    }
};
