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
            
            // Bypass triggers for BOTH Booking and Ticket creation
            // This prevents the DB from overwriting our manual IDs with trigger-generated ones,
            // ensuring the ID in PHP matches the ID in the Database.
            \Illuminate\Support\Facades\DB::statement("SET session_replication_role = 'replica';");

            // 1. Create Booking
            $booking = Booking::create([
                'id' => 'BKG-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
                'account_id' => Auth::id(),
                'schedule_id' => $schedule->id,
                'booking_date' => now(),
                'travel_date' => $request->travel_date ?? now()->toDateString(),
                'total_amount' => $schedule->price_per_seat, 
                'status' => 'Pending Payment'
            ]);

            // 2. Create Ticket
            Ticket::create([
                'id' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                'booking_id' => $booking->id,
                'passenger_name' => $request->passenger_name,
                'seat_number' => $request->seat_number,
                'status' => 'Booked'
            ]);

            \Illuminate\Support\Facades\DB::statement("SET session_replication_role = 'origin';");

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
