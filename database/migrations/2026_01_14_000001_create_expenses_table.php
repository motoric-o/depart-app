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
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('Approved')->after('amount'); // Approved, Pending, Rejected
            $table->string('type'); // 'reimbursement', 'operational', 'maintenance', 'salary', 'other'
            $table->date('date');
            $table->string('account_id')->nullable(); // Who recorded it
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->string('proof_file')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->timestamps();
        });

        DB::unprepared("
            -- CLEANUP FIRST
            DROP TRIGGER IF EXISTS trg_set_expense_id ON expenses;
            DROP FUNCTION IF EXISTS set_expense_id();
            DROP SEQUENCE IF EXISTS expenses_seq;

            -- CREATE FRESH
            CREATE SEQUENCE expenses_seq;
            CREATE FUNCTION set_expense_id() RETURNS TRIGGER AS $$
            BEGIN
                NEW.id := generate_custom_id('expenses_seq', 'EXP-', 6);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER trg_set_expense_id BEFORE INSERT ON expenses FOR EACH ROW EXECUTE FUNCTION set_expense_id();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_set_expense_id ON expenses;
            DROP FUNCTION IF EXISTS set_expense_id();
            DROP SEQUENCE IF EXISTS expenses_seq;
        ");
        Schema::dropIfExists('expenses');
    }
};
