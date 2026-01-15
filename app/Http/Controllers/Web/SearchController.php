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
        // 1. Get Dropdown Data
        $destinations = Destination::orderBy('city_name')->get();
        $busTypes = \App\Models\Bus::select('bus_type')->distinct()->pluck('bus_type');

        // 2. Start Query
        $query = Schedule::query();

        // 3. Apply Filters

        // Filter by Source (Flexible Search)
        if ($request->filled('from')) {
            $sourceInput = $request->from;
            // Try to find the city name if a code is passed
            $city = Destination::where('code', $sourceInput)->value('city_name');
            $searchTerm = $city ?? $sourceInput;

            $query->whereHas('route', function ($q) use ($searchTerm) {
                $q->where('source', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by Destination
        if ($request->filled('to')) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('destination_code', $request->to);
            });
        }

        // Filter by Date
        $travelDate = $request->input('date', date('Y-m-d'));
        if ($travelDate) {
            $query->whereDate('departure_time', $travelDate);
        }

        // Filter by Price
        if ($request->filled('min_price')) {
            $query->where('price_per_seat', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_seat', '<=', $request->max_price);
        }

        // Filter by Bus Type
        if ($request->filled('type')) {
            $types = (array)$request->input('type');
            $query->whereHas('bus', function ($q) use ($types) {
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
