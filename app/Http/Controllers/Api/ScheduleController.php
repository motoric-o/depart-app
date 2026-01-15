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
        \Illuminate\Support\Facades\Log::info('API Search Request Params:', $request->all());
        
        // Use Eloquent logic similar to Web\SearchController for consistency
        $query = Schedule::query();

        // 1. Filter by Source
        if ($request->filled('from')) {
            $sourceInput = $request->from;
            $query->whereHas('route', function ($q) use ($sourceInput) {
                // Check code first, then fallback
                $q->where('source_code', $sourceInput)
                  ->orWhere('source', $sourceInput)
                  ->orWhereHas('sourceDestination', function ($sq) use ($sourceInput) {
                      $sq->where('city_name', 'like', "%{$sourceInput}%");
                  });
            });
        }

        // 2. Filter by Destination
        if ($request->filled('to')) {
            $query->whereHas('route', function ($q) use ($request) {
                // Check code first
                $q->where('destination_code', $request->to);
            });
        }

        // 3. Filter by Date
        $travelDate = $request->input('date');
        if ($travelDate) {
             $query->whereDate('departure_time', $travelDate);
        }

        // 4. Filter by Price
        if ($request->filled('min_price')) {
            $query->where('price_per_seat', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_seat', '<=', $request->max_price);
        }

        // 5. Filter by Bus Type
        if ($request->filled('type')) {
            $types = (array)$request->input('type');
             $query->whereHas('bus', function ($q) use ($types) {
                $q->whereIn('bus_type', $types);
            });
        }

        $schedules = $query->with(['route.destination', 'route.sourceDestination', 'bus'])->get();

        // Transform data
        $results = $schedules->map(function ($s) {
            $scheduleDate = \Carbon\Carbon::parse($s->departure_time)->toDateString();
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
                'available_seats' => $s->getAvailableSeats($scheduleDate), // Use schedule's own date
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