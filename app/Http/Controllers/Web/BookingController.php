<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $schedule = Schedule::with(['bus', 'route.sourceDestination', 'route.destination'])->findOrFail($request->schedule_id);
        $date = $request->date;

        return view('customer.bookings.create', compact('schedule', 'date'));
    }
}
