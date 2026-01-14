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
            CREATE OR REPLACE VIEW view_available_trips AS
            SELECT 
                s.id as schedule_id, 
                r.source, 
                r.destination_code, 
                d.city_name as destination_name,
                s.departure_time, 
                b.bus_type, 
                s.price_per_seat
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN destinations d ON r.destination_code = d.code
            JOIN buses b ON s.bus_id = b.id
            WHERE s.remarks = 'Scheduled';
        ");

        DB::unprepared("
            CREATE OR REPLACE VIEW view_route_stats AS
            SELECT 
                r.id as route_id,
                CONCAT(r.source_code, ' - ', r.destination_code) as route_name,
                r.source as source_name,
                d.city_name as destination_name,
                r.destination_code,
                COUNT(b.id) as total_bookings,
                COALESCE(SUM(b.total_amount), 0) as total_revenue,
                AVG(s.price_per_seat) as average_ticket_price
            FROM routes r
            JOIN destinations d ON r.destination_code = d.code
            LEFT JOIN schedules s ON r.id = s.route_id
            LEFT JOIN bookings b ON s.id = b.schedule_id AND b.status = 'Confirmed'
            GROUP BY r.id, r.source, r.source_code, r.destination_code, d.city_name
        ");

        // View 2: Customer Booking Details (Denormalized)
        // Simplifies fetching booking history with all related info in one row
        DB::unprepared("
            CREATE OR REPLACE VIEW view_customer_bookings AS
            SELECT 
                b.id as booking_id,
                b.account_id,
                CONCAT(a.first_name, ' ', a.last_name) as customer_name,
                a.email,
                b.booking_date,
                b.travel_date,
                b.status as booking_status,
                b.total_amount,
                r.source as source_city,
                d.city_name as destination_city,
                s.departure_time,
                bus.bus_type,
                bus.bus_number,
                (SELECT COUNT(*) FROM tickets t WHERE t.booking_id = b.id) as seat_count
            FROM bookings b
            JOIN accounts a ON b.account_id = a.id
            JOIN schedules s ON b.schedule_id = s.id
            JOIN routes r ON s.route_id = r.id
            JOIN destinations d ON r.destination_code = d.code
            JOIN buses bus ON s.bus_id = bus.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS view_available_trips");
        DB::unprepared("DROP VIEW IF EXISTS view_route_stats");
        DB::unprepared("DROP VIEW IF EXISTS view_customer_bookings");
    }
};
