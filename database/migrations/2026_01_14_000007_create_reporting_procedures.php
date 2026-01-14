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
            DROP FUNCTION IF EXISTS sp_get_monthly_revenue(INT, INT);
            DROP FUNCTION IF EXISTS sp_get_owner_dashboard_stats();

            -- 5.1 MONTHLY REVENUE (Function)
            CREATE OR REPLACE FUNCTION sp_get_monthly_revenue(p_year INT, p_month INT)
            RETURNS TABLE (
                report_day DATE,
                daily_bookings BIGINT,
                daily_revenue DECIMAL
            ) 
            LANGUAGE plpgsql AS $$
            BEGIN
                RETURN QUERY
                SELECT 
                    DATE(transaction_date) as report_day,
                    COUNT(DISTINCT booking_id) as daily_bookings,
                    SUM(total_amount) as daily_revenue
                FROM transactions
                WHERE EXTRACT(YEAR FROM transaction_date) = p_year
                AND EXTRACT(MONTH FROM transaction_date) = p_month
                AND status = 'Success'
                AND type = 'Payment'
                GROUP BY DATE(transaction_date)
                ORDER BY report_day;
            END;
            $$;

            -- 5.2 OWNER DASHBOARD STATS (Function)
            CREATE OR REPLACE FUNCTION sp_get_owner_dashboard_stats()
            RETURNS TABLE (
                total_bookings BIGINT,
                total_revenue DECIMAL,
                total_customers BIGINT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    (SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'),
                    (SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE status = 'Success'),
                    (SELECT COUNT(*) FROM accounts WHERE account_type_id = (SELECT id FROM account_types WHERE name = 'Customer'));
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
            DROP FUNCTION IF EXISTS sp_get_owner_dashboard_stats();
            DROP FUNCTION IF EXISTS sp_get_monthly_revenue(INT, INT);
        ");
    }
};
