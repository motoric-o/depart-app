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

        // Start building query
        $query = Schedule::query();

        // Filter by Route Source
        if ($request->filled('from')) {
            $sourceCode = $request->from;
            $dest = Destination::where('code', $sourceCode)->first();
            
            // If we found a destination, try to match its name in the route 'source' string
            // Otherwise fall back to code match (just in case)
            $searchTerm = $dest ? $dest->city_name : $sourceCode;

            $query->whereHas('route', function ($q) use ($searchTerm) {
                $q->where('source', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by Route Destination
        if ($request->filled('to')) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('destination_code', $request->to);
            });
        }

        // Filter by Bus Type (Class)
        if ($request->filled('type')) {
            $types = $request->input('type'); // Expecting array
            // If it comes as a string (e.g. from single checkbox or query param), convert to array
            if (!is_array($types)) {
                $types = [$types];
            }
            
            $query->whereHas('bus', function ($q) use ($types) {
                $q->whereIn('bus_type', $types);
            });
        }

        // Filter by Price Range
        if ($request->filled('min_price')) {
            $query->where('price_per_seat', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_seat', '<=', $request->max_price);
        }

        // Filter by Active Status
        // Seeder uses 'Scheduled', checking for that or 'active' to be safe
        $query->whereIn('status', ['Scheduled', 'active']);

        // Eager load relationships
        $schedules = $query->with(['route.destination', 'route.sourceDestination', 'bus'])->get();
        
        // Pass the Travel Date as well if provided, so the view can check availability
        $travelDate = $request->input('date', date('Y-m-d'));

        // Get unique bus types for the filter sidebar
        $busTypes = \App\Models\Bus::select('bus_type')->distinct()->pluck('bus_type');

        return view('customer.schedules.index', compact('schedules', 'destinations', 'travelDate', 'busTypes'));
    }
}
