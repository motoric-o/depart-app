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
        
        // Exclude Incomplete Schedules
        $query->whereNotNull('bus_id')
              ->whereNotNull('route_id')
              ->where('remarks', 'not like', '%Canceled%')
              ->where('remarks', 'not like', '%Pending%');

        // Filter by Source
        if ($request->filled('from')) {
            $sourceInput = $request->from;
            
            // The input from home page is a Code (e.g., JKT)
            // But we might also want to support flexible search later if we change input type.
            // Since Route.source is a Code, we should match it directly.
            
            $query->whereHas('route', function ($q) use ($sourceInput) {
                // Check if it matches the code directly (using source_code column)
                $q->where('source_code', $sourceInput)
                  // Fallback: Check 'source' column just in case
                  ->orWhere('source', $sourceInput)
                  // Fallback: Check relationship name
                  ->orWhereHas('sourceDestination', function ($sq) use ($sourceInput) {
                      $sq->where('city_name', 'like', "%{$sourceInput}%");
                  });
            });
        }

        // Filter by Destination
        if ($request->filled('to')) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('destination_code', $request->to);
            });
        }

        // Filter by Date
        $travelDate = $request->input('date'); // Remove default date('Y-m-d')
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
        $schedules = $schedules->map(function($s) {
            // Use specific schedule date for availability check
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
                'available_seats' => $s->getAvailableSeats($scheduleDate),
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
            return response()->json($schedules);
        }

        return view('customer.schedules.index', compact('schedules', 'destinations', 'travelDate', 'busTypes'));
    }
}
