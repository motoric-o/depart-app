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
        
        // Get unique bus types for the filter sidebar
        $busTypes = \App\Models\Bus::select('bus_type')->distinct()->pluck('bus_type');

        if ($request->ajax()) {
            return view('customer.schedules.partials.results', compact('schedules', 'travelDate'));
        }

        return view('customer.schedules.index', compact('schedules', 'destinations', 'travelDate', 'busTypes'));
    }
}
