<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Ticket;

class ScheduleController extends Controller
{
    public function search(Request $request)
    {
        // 1. Validate the User's Search
        $request->validate([
            'from' => 'required|string', // e.g., 'Jakarta'
            'to'   => 'required|string', // e.g., 'Bandung'
            'date' => 'required|date',   // e.g., '2025-12-25'
        ]);

        $travelDate = $request->date;

        // 2. Find Schedules that match the Route
        // We look for schedules where the Route matches the source/destination cities
        $schedules = Schedule::whereHas('route', function($q) use ($request) {
            $q->where('source', $request->from)
              ->whereHas('destination', function($subQ) use ($request) {
                  $subQ->where('city_name', $request->to);
              });
        })
        ->with(['bus', 'route', 'route.destination']) // Eager load details
        ->get();

        // 3. The "Occupied" Logic (This is where we put it!)
        // We loop through each schedule to calculate how many seats are left *for that specific date*.
        // Inside ScheduleController::search method

        $results = $schedules->map(function ($schedule) use ($travelDate) {
            // We just call the method we made in the Model
            $schedule->available_seats = $schedule->getAvailableSeats($travelDate);
            return $schedule;
        });

        return response()->json($results);
    }

    // BONUS: Get specific taken seat numbers (e.g., ['1A', '2B'])
    // You will call this when the user clicks "Select Seats"
    public function getTakenSeats(Request $request, $schedule_id)
    {
        $request->validate(['date' => 'required|date']);

        $takenSeats = Ticket::whereHas('booking', function ($query) use ($schedule_id, $request) {
            $query->where('schedule_id', $schedule_id)
                  ->where('travel_date', $request->date)
                  ->where('status', '!=', 'cancelled');
        })->pluck('seat_number'); // Just get the list of seat numbers

        return response()->json(['taken_seats' => $takenSeats]);
    }
}