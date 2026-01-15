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
        // Parameters for SP
        $sourceCode = $request->input('from') ?: ''; 
        $destCode   = $request->input('to') ?: '';   
        $travelDate = $request->input('date');
        if (empty($travelDate)) {
            $travelDate = date('Y-m-d');
        }
        
        $minPrice   = $request->filled('min_price') ? $request->input('min_price') : 0;
        $maxPrice   = $request->filled('max_price') ? $request->input('max_price') : 99999999;
        
        // Execute Stored Procedure
        $rawSchedules = \Illuminate\Support\Facades\DB::select(
            "SELECT * FROM sp_search_trips(?, ?, ?, ?, ?)", 
            [$sourceCode, $destCode, $travelDate, $minPrice, $maxPrice]
        );

        $scheduleIds = array_column($rawSchedules, 'schedule_id');
        
        $query = Schedule::whereIn('id', $scheduleIds)
                        ->with(['route.destination', 'route.sourceDestination', 'bus']);

        if ($request->filled('type')) {
            $types = $request->input('type');
            if (!is_array($types)) {
                $types = [$types];
            }
            $query->whereHas('bus', function($q) use ($types) {
                $q->whereIn('bus_type', $types);
            });
        }
                        
        $schedules = $query->get();

        // Transform data
        $results = $schedules->map(function ($s) use ($travelDate) {
            return [
                'id' => $s->id,
                'departure_time' => $s->departure_time, 
                'arrival_time' => $s->arrival_time,
                'estimated_duration' => $s->estimated_duration,
                'price_per_seat' => $s->price_per_seat,
                'formatted_price' => number_format($s->price_per_seat, 0, ',', '.'),
                'departure_format' => \Carbon\Carbon::parse($s->departure_time)->format('H:i'),
                'arrival_format' => \Carbon\Carbon::parse($s->arrival_time)->format('H:i'),
                'duration_hour' => \Carbon\Carbon::parse($s->estimated_duration)->format('H'),
                'duration_minute' => \Carbon\Carbon::parse($s->estimated_duration)->format('i'),
                'available_seats' => $s->getAvailableSeats($travelDate),
                'bus' => [
                    'bus_number' => $s->bus->bus_number,
                    'bus_type' => $s->bus->bus_type,
                ],
                'route' => [
                    'source' => $s->route->sourceDestination->city_name ?? $s->route->source,
                    'destination' => $s->route->destination->city_name ?? $s->route->destination_code,
                ]
            ];
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