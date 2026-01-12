<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Notifications\BookingConfirmed;
use Carbon\Carbon;

class BookingController extends Controller
{
    // POST /api/bookings
    public function store(Request $request)
    {
        // 1. Validate the Request
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'travel_date' => 'required|date|after_or_equal:today',
            'seats'       => 'required|array|min:1',
            'seats.*'     => 'required|string',
        ]);

        // 2. Fetch the Schedule (to get the real price)
        $schedule = Schedule::findOrFail($request->schedule_id);

        // 3. Calculate Total Price (Server Side)
        $totalPrice = $schedule->price_per_seat * count($request->seats);
        
        // 4. Atomic Booking via Stored Procedure
        try {
            // "SELECT sp_create_booking_atomic(?, ?, ?, ?, ?)"
            // Postgres array syntax for seats: needs to be formatted as {seat1, seat2}
            $seatsArray = '{' . implode(',', $request->seats) . '}';
            
            $results = DB::select("SELECT sp_create_booking_atomic(?, ?, ?, ?::text[], ?) as booking_id", [
                $request->user()->id,
                $request->schedule_id,
                $request->travel_date,
                $seatsArray,
                $totalPrice
            ]);
            
            $bookingId = $results[0]->booking_id;
            
            // Fetch the created booking to return to user
            $booking = Booking::with(['schedule.route.destination', 'tickets'])->findOrFail($bookingId);
            
            // Send Notification
            $request->user()->notify(new BookingConfirmed($booking));

            return response()->json([
                'message' => 'Booking successful',
                'data'    => $booking
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle custom exception "One or more seats are no longer available"
            if (str_contains($e->getMessage(), 'seats are no longer available')) {
                return response()->json(['message' => 'One or more selected seats are already booked.'], 409);
            }
            throw $e;
        }
    }

    // GET /api/my-bookings
    public function index(Request $request)
    {
        // Use the Denormalized View for better performance and simpler query
        $bookings = \App\Models\CustomerBooking::where('account_id', $request->user()->id)
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json($bookings);
    }

    public function cancel(Request $request, $id)
    {
        // 1. Find the booking belonging to the logged-in user
        $booking = Booking::where('id', $id)
                        ->where('account_id', $request->user()->id)
                        ->with(['schedule', 'payment']) // Load relations we need to check
                        ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        // 2. Check if it's already cancelled or completed
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 400);
        }
        if ($booking->status === 'completed') {
            return response()->json(['message' => 'Cannot cancel a completed trip.'], 400);
        }

        // 3. Time Limit Check (Optional but Recommended)
        // Combine the date and time to create a Carbon object
        // Assuming booking has 'travel_date' and schedule has 'departure_time'
        // Combine the date and time to create a Carbon object
        // We use travel_date from booking and time from schedule
        $departureTimestamp = Carbon::parse($booking->travel_date . ' ' . Carbon::parse($booking->schedule->departure_time)->format('H:i:s'));
        
        // If departure is less than 24 hours from now
        if ($departureTimestamp->lessThan(now()->addHours(24))) {
            return response()->json(['message' => 'Cancellation is only allowed 24 hours before departure.'], 400);
        }

        // 4. Atomic Booking Cancellation via Stored Procedure
        DB::statement("CALL sp_cancel_booking_atomic(?)", [$booking->id]);

        return response()->json(['message' => 'Booking cancelled successfully. Refund processing started.']);
    }
}