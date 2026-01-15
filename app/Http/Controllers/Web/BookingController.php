<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        // $request->validate([
        //     'schedule_id' => 'required',
        //     'date' => 'required|date',
        // ]);

        $schedule = Schedule::with(['route.destination', 'route.sourceDestination', 'bus'])
                           ->find($request->schedule_id);
                           
        if (!$schedule) {
            abort(404, 'Schedule not found');
        }

        $travelDate = $request->date;

        // Fetch occupied seats (Robust Method - PHP Filter EVERYTHING)
        // 1. Fetch ALL bookings for this schedule (ignoring date/status) to bypass DB query quirks
        $allBookings = Booking::where('schedule_id', $schedule->id)->get();

        // 2. Filter in PHP
        $validBookingIds = $allBookings->filter(function($b) use ($travelDate) {
            // Robust Date Check (compare first 10 chars YYYY-MM-DD)
            $dateMatch = substr($b->travel_date, 0, 10) === substr($travelDate, 0, 10);
            
            // Robust Status Check
            $s = trim($b->status);
            $statusMatch = in_array($s, ['Booked', 'Pending Payment', 'Confirmed', 'Pending']);

            return $dateMatch && $statusMatch;
        })->pluck('id');

        // 3. Fetch Tickets for these bookings
        $occupiedSeats = Ticket::whereIn('booking_id', $validBookingIds)
                               ->where('status', '!=', 'Cancelled')
                               ->pluck('seat_number')
                               ->map(function($seat) { return trim($seat); })
                               ->toArray();

        return view('customer.booking.create', compact('schedule', 'travelDate', 'occupiedSeats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required',
            'travel_date' => 'required|date',
            'seat_number' => 'required|string',
            'passenger_name' => 'required|string',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);

        $bookingId = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $schedule) {
            
            // 1. Create Booking (Let DB Trigger handle ID)
            $booking = Booking::create([
                // 'id' => removed to let Trigger generate it
                'account_id' => Auth::id(),
                'schedule_id' => $schedule->id,
                'booking_date' => now(),
                'travel_date' => $request->travel_date ?? now()->toDateString(),
                'total_amount' => $schedule->price_per_seat, 
                'status' => 'Pending Payment'
            ]);

            // 1b. Refetch Booking ID because Eloquent + Triggers + non-incrementing ID = missing ID
            // We fetch the latest booking for this user/schedule created just now.
            $booking = Booking::where('account_id', Auth::id())
                            ->where('schedule_id', $schedule->id)
                            ->orderBy('created_at', 'desc')
                            ->firstOrFail();

            // 2. Create Ticket (Let DB Trigger handle ID)
            Ticket::create([
                // 'id' => removed to let Trigger generate it
                'booking_id' => $booking->id,
                'passenger_name' => $request->passenger_name,
                'seat_number' => $request->seat_number,
                'status' => 'Booked'
            ]);

            return $booking->id;
        });

        return redirect()->route('booking.payment', ['booking_id' => $bookingId]);
    }

    public function payment($booking_id)
    {
        // Debugging 404
        $booking = Booking::with(['schedule.route.destination', 'schedule.route.sourceDestination', 'schedule.bus', 'tickets'])
                        // ->where('account_id', Auth::id()) // Temporarily disable ownership check
                        ->find($booking_id);

        if (!$booking) {
            abort(404, 'Booking ID ' . $booking_id . ' not found in database.');
        }

        return view('customer.booking.payment', compact('booking'));
    }
    public function completePayment($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);
        
        // Optimize: In real world, verify payment gateway callback. 
        // Here we simulate success.
        $booking->update(['status' => 'Booked']);
        
        // Update tickets availability if needed (though they are created as 'Booked' initially)
        $booking->tickets()->update(['status' => 'Valid']);

        return view('customer.booking.success', compact('booking'));
    }

    public function ticket($booking_id)
    {
        $booking = Booking::with(['schedule.bus', 'schedule.route.sourceDestination', 'schedule.route.destination', 'tickets'])
            ->where('account_id', Auth::id())
            ->findOrFail($booking_id);

        return view('customer.booking.ticket', compact('booking'));
    }

    public function history()
    {
        $bookings = Booking::with(['schedule.bus', 'schedule.route.sourceDestination', 'schedule.route.destination'])
            ->where('account_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.booking.history', compact('bookings'));
    }
}
