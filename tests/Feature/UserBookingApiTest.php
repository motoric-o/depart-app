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

class UserBookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_my_bookings()
    {
        // 1. Arrange
        $user = Account::factory()->create();
        $otherUser = Account::factory()->create();

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

        // Booking 1: Belonging to the user
        $myBooking = Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => now()->addDay(),
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);

        // Booking 2: Belonging to someone else
        Booking::create([
            'account_id' => $otherUser->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => now()->addDay(),
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);

        // 2. Act
        $response = $this->actingAs($user)->getJson('/api/my-bookings');

        // 3. Assert
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals($myBooking->id, $response->json('0.id'));
    }

    public function test_user_can_cancel_booking()
    {
        // 1. Arrange
        $user = Account::factory()->create();

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

        // Booking must be more than 24h away to cancel (logic in controller: departureTimestamp < NOW + 24h -> Fail)
        // Controller Logic: if ($departureTimestamp->lessThan(now()->addHours(24))) { Error }
        // So we need departure to be > 24h from now.
        // Travel Date: 2 days from now.
        
        $travelDate = now()->addDays(2)->toDateString();

        $booking = Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => $travelDate,
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);

        Ticket::create(['booking_id' => $booking->id, 'passenger_name' => 'P1', 'seat_number' => '1A', 'status' => 'confirmed']);

        // 2. Act
        $response = $this->actingAs($user)->postJson("/api/bookings/{$booking->id}/cancel");

        // 3. Assert
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Booking cancelled successfully. Refund processing started.']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);

        $this->assertDatabaseHas('tickets', [
            'booking_id' => $booking->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_cannot_cancel_booking_less_than_24h_before()
    {
        $user = Account::factory()->create();

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

        // Travel Date: Tomorrow (Less than 24h if ran late in the day vs 10am... tricky)
        // If now is 23:00 (11PM), tomorrow 10:00 is < 12 hours away.
        // If we set travel_date to NOW's date, it's definitely < 24h.
        
        $booking = Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => now()->toDateString(), // TODAY
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);

        $response = $this->actingAs($user)->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Cancellation is only allowed 24 hours before departure.']);
    }
}
