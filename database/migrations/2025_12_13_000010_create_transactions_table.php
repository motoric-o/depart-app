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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->string('customer_name')->nullable()->after('account_id');
            $table->string('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings');
            $table->string('ticket_id')->nullable();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onUpdate('cascade')->onDelete('set null');
            $table->dateTime('transaction_date');
            $table->string('payment_method');
            $table->decimal('sub_total', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('extra_fees', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('type'); 
            $table->string('status')->default('Success');
            $table->timestamps();
        });

        DB::unprepared("
            -- CLEANUP FIRST
            DROP TRIGGER IF EXISTS trg_set_transaction_id ON transactions;
            DROP FUNCTION IF EXISTS set_transaction_id();
            DROP SEQUENCE IF EXISTS transactions_seq;

            -- CREATE FRESH
            CREATE SEQUENCE transactions_seq;
            CREATE FUNCTION set_transaction_id() RETURNS TRIGGER AS $$
            BEGIN
                NEW.id := generate_custom_id('transactions_seq', 'TRX-', 6);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_transaction_id BEFORE INSERT ON transactions FOR EACH ROW EXECUTE FUNCTION set_transaction_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_transaction_id ON transactions;
            DROP FUNCTION IF EXISTS set_transaction_id();
            DROP SEQUENCE IF EXISTS transactions_seq;
        ");
        Schema::dropIfExists('transactions');
    }
};
