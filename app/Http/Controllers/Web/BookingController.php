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

        return view('customer.booking.create', compact('schedule', 'travelDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required',
            // 'travel_date' => 'required|date',
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
    public function history()
    {
        $bookings = Booking::with(['schedule.bus', 'schedule.route.sourceDestination', 'schedule.route.destination'])
            ->where('account_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.booking.history', compact('bookings'));
    }
}
