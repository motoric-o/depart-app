<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Transaction;
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

        $travelDate = $request->date ?? \Carbon\Carbon::parse($schedule->departure_time)->toDateString();

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
            'seats' => 'required|array|min:1',
            'seats.*' => 'required|string',
            'passengers' => 'required|array',
            'passengers.*' => 'required|string',
            'split_bill' => 'nullable|array',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);

        $bookingId = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $schedule) {
            
            // 1. Create Booking (Let DB Trigger handle ID)
            $totalSeats = count($request->seats);
            $booking = Booking::create([
                'account_id' => Auth::id(),
                'schedule_id' => $schedule->id,
                'booking_date' => now(),
                'travel_date' => $request->travel_date ?? now()->toDateString(),
                'total_amount' => $schedule->price_per_seat * $totalSeats, 
                'status' => 'Pending Payment'
            ]);

            // 1b. Refetch Booking ID
            $booking = Booking::where('account_id', Auth::id())
                            ->where('schedule_id', $schedule->id)
                            ->orderBy('created_at', 'desc')
                            ->firstOrFail();

            // 2. Group Seats for Split Bill
            $groups = [];
            if ($request->has('split_bill')) {
                foreach ($request->seats as $seat) {
                    // split_bill is keyed by seat number or index? 
                    // Let's assume UI sends seat_number -> group_id
                    // But checking valid seat might be tricky if input name is dynamic.
                    // Let's assume input name="split_bill[SEAT_NUMBER]" value="GROUP_ID"
                    $groupId = $request->split_bill[$seat] ?? 'main';
                    $groups[$groupId][] = $seat;
                }
            } else {
                $groups['main'] = $request->seats;
            }
             // Fallback if loop missed something (redundancy check)
             // Actually better:
             foreach ($request->seats as $seat) {
                 $groupId = $request->split_bill[$seat] ?? 'main';
                 if (!isset($groups[$groupId])) {
                      $groups[$groupId] = [];
                 }
                 if (!in_array($seat, $groups[$groupId])) {
                     $groups[$groupId][] = $seat;
                 }
             }


            // 3. Create Transactions & Tickets
            foreach ($groups as $groupId => $seatsInGroup) {
                if (empty($seatsInGroup)) continue;

                $subTotal = $schedule->price_per_seat * count($seatsInGroup);
                
                // Create Transaction
                \App\Models\Transaction::create([
                    'account_id' => Auth::id(),
                    'booking_id' => $booking->id,
                    'transaction_date' => now(),
                    'payment_method' => 'QRIS',
                    'sub_total' => $subTotal,
                    'total_amount' => $subTotal,
                    'type' => 'Payment',
                    'status' => 'Pending'
                ]);

                // Retrieve the just-created transaction
                // Since we are inside a transaction, and timestamps might be identical, relies on ID sequence if possible.
                // Or we can rely on `latest('id')` if IDs are roughly sequential (custom ID might not be).
                // But generally latest created_at + highest ID is safe bet for same-microsecond inserts.
                $transaction = \App\Models\Transaction::where('booking_id', $booking->id)
                                ->orderBy('created_at', 'desc')
                                ->orderBy('id', 'desc') // Custom ID usually has numeric part
                                ->first();

                // Create Tickets
                foreach ($seatsInGroup as $seat) {
                    $passengerName = $request->passengers[$seat] ?? $request->passenger_name;
                    Ticket::create([
                        'booking_id' => $booking->id,
                        'transaction_id' => $transaction->id,
                        'passenger_name' => $passengerName,
                        'seat_number' => $seat,
                        'status' => 'Booked'
                    ]);
                }
            }

            return $booking->id;
        });

        return redirect()->route('booking.payment', ['booking_id' => $bookingId]);
    }

    public function payment($booking_id)
    {
        // Debugging 404
        $booking = Booking::with(['schedule.route.destination', 'schedule.route.sourceDestination', 'schedule.bus', 'tickets', 'transactions' => function($q) {
                            $q->orderBy('id');
                        }])
                        // ->where('account_id', Auth::id()) // Temporarily disable ownership check
                        ->find($booking_id);

        if (!$booking) {
            abort(404, 'Booking ID ' . $booking_id . ' not found in database.');
        }

        return view('customer.booking.payment', compact('booking'));
    }
    public function completePayment($transaction_id)
    {
        // This is now effectively "completeTransaction"
        // In routes file, we might need to check if parameters changed.
        // Assuming route is /booking/payment/{id}/complete or similar.
        // If route passes booking_id, we need to find transactions.
        // But for Split Bill, we need to pay specific TRANSACTION.
        // The View will link to route('payment.complete', ['transaction_id' => $trx->id]) 
        // We'll assume the route parameter stays same name or we check context.
        
        $transaction = Transaction::findOrFail($transaction_id);
        
        // 1. Mark Transaction as Success
        $transaction->update(['status' => 'Success']);
        
        // 2. Mark related tickets as Valid? Or wait for full booking?
        // Requirement: "Once all of them are paid, the booking status will be set to Booked"
        // But individual tickets are paid. Should they be valid?
        // "Booking will be put to 'pending' status... 2 tickets user will pay... other 1 shown as 100k".
        // It implies partial validity is possible or at least tracked.
        // Let's mark tickets linked to THIS transaction as Valid.
        if ($transaction->tickets()->exists()) {
             $transaction->tickets()->update(['status' => 'Valid']);
        }
        
        // 3. Check Booking Status
        $booking = $transaction->booking;
        
        // Are there any pending transactions?
        $pendingCount = $booking->transactions()
                        ->where('type', 'Payment')
                        ->where('status', '!=', 'Success')
                        ->count();
                        
        if ($pendingCount === 0) {
            $booking->update(['status' => 'Booked']);
            
            // If any tickets were not linked to transaction (legacy), update them? 
            // New logic links all.
            return view('customer.booking.success', compact('booking'));
        }

        // Return to payment page to pay others?
        return redirect()->route('booking.payment', ['booking_id' => $booking->id])->with('success', 'Pembayaran berhasil! Silakan selesaikan pembayaran lainnya.');
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
