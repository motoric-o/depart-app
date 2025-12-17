<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\Destination;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_schedules()
    {
        // 1. Arrange
        Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        $route = Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $bus = Bus::factory()->create(['capacity' => 40]);
        
        $today = now()->toDateString();
        
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(8, 0),
            'arrival_time' => now()->setTime(12, 0),
            'price_per_seat' => 150000
        ]);

        // 2. Act
        $response = $this->getJson("/api/schedules/search?from=Jakarta&to=Bandung&date=$today");

        // 3. Assert
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        
        // available_seats should be 40 (capacity) since no bookings yet
        $response->assertJsonFragment(['available_seats' => 40]);
        $response->assertJsonFragment(['price_per_seat' => '150000.00']);
    }

    public function test_search_returns_empty_if_route_does_not_match()
    {
        Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        $route = Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $bus = Bus::factory()->create();
        
        Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(8, 0),
            'arrival_time' => now()->setTime(12, 0),
            'price_per_seat' => 150000
        ]);

        $response = $this->getJson("/api/schedules/search?from=Surabaya&to=Bandung&date=" . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(0, $response->json());
    }

    public function test_can_get_taken_seats()
    {
        // 1. Arrange
        Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        $route = Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $bus = Bus::factory()->create();
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(10, 0),
            'arrival_time' => now()->setTime(14, 0),
            'price_per_seat' => 100000
        ]);

        $user = Account::factory()->create();
        $travelDate = now()->addDays(2)->toDateString();

        // Create a booking with tickets
        $booking = Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => $travelDate,
            'status' => 'confirmed',
            'total_amount' => 200000
        ]);

        Ticket::create(['booking_id' => $booking->id, 'passenger_name' => 'P1', 'seat_number' => '1A', 'status' => 'confirmed']);
        Ticket::create(['booking_id' => $booking->id, 'passenger_name' => 'P2', 'seat_number' => '1B', 'status' => 'confirmed']);

        // 2. Act
        $response = $this->getJson("/api/schedules/{$schedule->id}/seats?date=$travelDate");

        // 3. Assert
        $response->assertStatus(200)
                 ->assertJsonStructure(['taken_seats']);
        
        $taken = $response->json('taken_seats');
        $this->assertTrue(in_array('1A', $taken));
        $this->assertTrue(in_array('1B', $taken));
        $this->assertCount(2, $taken);
    }
}
