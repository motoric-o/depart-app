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
        Schema::create('accounts', function (Blueprint $table) {
            $table->string('id')->primary(); // A-2025120001
            $table->foreignId('account_type_id')->constrained();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('password_hash');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION set_account_id() RETURNS TRIGGER AS $$
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
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_account_id BEFORE INSERT ON accounts FOR EACH ROW EXECUTE FUNCTION set_account_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_account_id ON accounts;
            DROP FUNCTION IF EXISTS set_account_id();
            -- Note: We generally do not drop the counter table's sequence logic here 
            -- because it's shared, but we can if we want a full wipe.
        ");
        Schema::dropIfExists('accounts');
    }
};
