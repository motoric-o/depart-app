<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Destination;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Get all destinations for the dropdowns
        $destinations = Destination::orderBy('city_name')->get();

        // Parameters for SP
        $sourceCode = $request->input('from') ?: ''; // Default to empty string if null
        $destCode   = $request->input('to') ?: '';   // Default to empty string if null
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

        // Convert raw results to Eloquent Collection (or simple objects)
        // Since the view expects Schedule models with relations, we might need to fully hydrate them
        // OR simply fetch the IDs from SP and use Eloquent to load relations.
        // Fetching IDs is safer for relation compatibility.
        
        $scheduleIds = array_column($rawSchedules, 'schedule_id');
        
        $query = Schedule::whereIn('id', $scheduleIds)
                        ->with(['route.destination', 'route.sourceDestination', 'bus']);

        // Filter by Bus Type (PHP side since SP mostly handles core filtering)
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

        // Transform data for JSON/View consistency
        $schedules = $schedules->map(function($s) use ($travelDate) {
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
        
        // Get unique bus types for the filter sidebar
        $busTypes = \App\Models\Bus::select('bus_type')->distinct()->pluck('bus_type');

        if ($request->ajax()) {
            // Legacy AJAX support might be removed or kept. 
            // If we switch to full Alpine, we might not need this partial return effectively.
            // But let's keep it returning JSON if we wanted, or just remove it.
            // valid JSON response for AJAX requests (if any legacy ones remain)
            return response()->json($schedules);
        }

        return view('customer.schedules.index', compact('schedules', 'destinations', 'travelDate', 'busTypes'));
    }
}
