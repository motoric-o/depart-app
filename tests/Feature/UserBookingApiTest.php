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
        Account::factory()->create(['email' => 'u1@test.com']);
        $user = Account::where('email', 'u1@test.com')->first();
        
        Account::factory()->create(['email' => 'u2@test.com']);
        $otherUser = Account::where('email', 'u2@test.com')->first();

        Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $route = Route::where('source', 'Jakarta')->where('destination_code', 'BDG')->first();
        
        Bus::factory()->create(['bus_number' => 'B-TEST-001']);
        $bus = Bus::where('bus_number', 'B-TEST-001')->first();
        
        Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(10, 0),
            'arrival_time' => now()->setTime(14, 0),
            'price_per_seat' => 100000
        ]);
        $schedule = Schedule::latest()->first();

        // Booking 1: Belonging to the user
        Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => now()->addDay(),
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);
        $myBooking = Booking::latest()->first();

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
        Account::factory()->create(['email' => 'cancel@test.com']);
        $user = Account::where('email', 'cancel@test.com')->first();

        Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $route = Route::where('source', 'Jakarta')->where('destination_code', 'BDG')->first();
        
        Bus::factory()->create(['bus_number' => 'B-TEST-002']);
        $bus = Bus::where('bus_number', 'B-TEST-002')->first();
        
        Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(10, 0),
            'arrival_time' => now()->setTime(14, 0),
            'price_per_seat' => 100000
        ]);
        $schedule = Schedule::latest()->first();

        // Booking must be more than 24h away to cancel
        $travelDate = now()->addDays(2)->toDateString();
        
        Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => $travelDate,
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);
        $booking = Booking::latest()->first();

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
        Account::factory()->create(['email' => 'late@test.com']);
        $user = Account::where('email', 'late@test.com')->first();

        Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        $route = Route::where('source', 'Jakarta')->where('destination_code', 'BDG')->first();
        
        Bus::factory()->create(['bus_number' => 'B-TEST-003']);
        $bus = Bus::where('bus_number', 'B-TEST-003')->first();
        
        Schedule::create([
            'route_id' => $route->id,
            'bus_id' => $bus->id,
            'departure_time' => now()->setTime(10, 0),
            'arrival_time' => now()->setTime(14, 0),
            'price_per_seat' => 100000
        ]);
        $schedule = Schedule::latest()->first();

        // Travel Date: Tomorrow (Less than 24h if ran late in the day vs 10am... tricky)
        // If now is 23:00 (11PM), tomorrow 10:00 is < 12 hours away.
        // If we set travel_date to NOW's date, it's definitely < 24h.
        
        Booking::create([
            'account_id' => $user->id,
            'schedule_id' => $schedule->id,
            'booking_date' => now(),
            'travel_date' => now()->toDateString(), // TODAY
            'status' => 'confirmed',
            'total_amount' => 100000
        ]);
        $booking = Booking::latest()->first();

        $response = $this->actingAs($user)->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Cancellation is only allowed 24 hours before departure.']);
    }
}
