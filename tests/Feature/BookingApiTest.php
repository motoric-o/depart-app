<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\Destination;
use App\Models\Route;
use App\Models\Schedule;
use App\Notifications\BookingConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    // This trait resets the DB before every single test function
    use RefreshDatabase;

    public function test_user_can_create_booking_and_get_notification()
    {
        // 1. ARRANGEMENT: Create the world
        // We need a user, a bus, and a schedule to book
        Notification::fake(); // Tells Laravel: "Don't actually send emails/DB notifications, just record them"

        $user = Account::factory()->create();
        
        // We create these manually or use factories if you have them
        $destination = Destination::firstOrCreate(['code' => 'BDG'], ['city_name' => 'Bandung']);
        

        $route = Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $bus = Bus::factory()->create(); 
        
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(8, 0, 0),
            'arrival_time' => now()->setTime(12, 0, 0),
            'price_per_seat' => 100000
        ]);

        // 2. ACTION: Make the API Request
        // actingAs($user) logs the user in for this request
        $response = $this->actingAs($user)->postJson('/api/bookings', [
            'schedule_id' => $schedule->id,
            'travel_date' => now()->addDay()->toDateString(), // Tomorrow
            'seats' => ['1A', '1B'], // Booking 2 seats
        ]);

        // 3. ASSERTION: Check if it worked
        
        // Check API Response (201 Created)
        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Booking successful');

        // Check Database: Was the Booking created?
        $this->assertDatabaseHas('bookings', [
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'total_amount' => 200000, // 100k * 2 seats
            'status' => 'confirmed'
        ]);

        // Check Database: Were the tickets created?
        $this->assertDatabaseHas('tickets', ['seat_number' => '1A']);
        $this->assertDatabaseHas('tickets', ['seat_number' => '1B']);

        // Check Notification: Was it sent?
        Notification::assertSentTo(
            $user,
            BookingConfirmed::class
        );
    }
}