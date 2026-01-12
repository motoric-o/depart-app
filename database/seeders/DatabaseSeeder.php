<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountType;
use App\Models\Destination;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Account;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Transaction;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Static Lookup Tables
        $this->call([
            AccountTypeSeeder::class,
            DestinationSeeder::class,
            RouteSeeder::class,
        ]);

        // 2. Create Fleet
        // We create them, but we won't use the returned objects directly 
        // because their IDs are empty in PHP memory.
        Bus::factory(10)->create();
        // Fetch them back from DB so we have the IDs (BUS001, etc.)
        $buses = Bus::all(); 

        // 3. Fetch Routes (Seeded via RouteSeeder)
        $routes = Route::all();

        // 4. Create Users
        $adminType = AccountType::where('name', 'Admin')->first();
        $custType = AccountType::where('name', 'Customer')->first();

        // Create Admin
        if (!Account::where('email', 'admin@busapp.com')->exists()) {
            Account::factory()->create([
                'account_type_id' => $adminType->id,
                'email' => 'admin@busapp.com',
                'first_name' => 'Super',
                'last_name' => 'Admin'
            ]);
        }

        // Create Manual Account (Rico)
        Account::factory()->rico()->create();
        Account::factory()->jojo()->create();
        Account::factory()->jason()->create();

        // Create Customers
        Account::factory(5)->create([
            'account_type_id' => $custType->id
        ]);
        // FETCH THEM BACK TO GET IDs (C-2025...)
        $customers = Account::where('account_type_id', $custType->id)->get();

        // 5. Create Schedules (Trips)
        foreach(range(1, 50) as $i) {
            // Pick a random route from seeded routes
            $selectedRoute = $routes->random();

            Schedule::factory()->create([
                'route_id' => $selectedRoute->id,
                'bus_id' => $buses->random()->id,
            ]);
        }
        
        // FETCH SCHEDULES BACK
        $schedules = Schedule::all();

        // 6. Simulate Booking Flow
        foreach($schedules as $schedule) {
            // Only book a few schedules, not all
            if (rand(0, 1) == 0) continue;

            $customer = $customers->random();

            // Create Booking
            Booking::create([
                'account_id' => $customer->id,
                'schedule_id' => $schedule->id,
                'booking_date' => now(),
                'travel_date' => $schedule->departure_time,
                'total_amount' => $schedule->price_per_seat,
                'status' => 'Confirmed'
            ]);
            
            // FETCH BOOKING BACK (To get ID: BK-2025-...)
            // We find the one we just made for this user/schedule
            $booking = Booking::where('account_id', $customer->id)
                              ->where('schedule_id', $schedule->id)
                              ->latest()
                              ->first();

            // Create Ticket
            Ticket::create([
                'booking_id' => $booking->id,
                'passenger_name' => $customer->first_name,
                'seat_number' => '1A',
                'status' => 'Valid'
            ]);
            
            // Note: Ticket ID is predictable, but strict fetching isn't needed 
            // for Transaction unless we want to link it precisely.
            // For now, let's link the transaction to the booking.

            // Create Transaction
            Transaction::create([
                'account_id' => $customer->id,
                'booking_id' => $booking->id,
                'ticket_id' => null, // Full payment
                'transaction_date' => now(),
                'payment_method' => 'Credit Card',
                'sub_total' => $schedule->price_per_seat,
                'total_amount' => $schedule->price_per_seat,
                'type' => 'Payment',
                'status' => 'Success'
            ]);
        }
    }
}