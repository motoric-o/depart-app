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

        // 4. Eager Load & Execute
        $schedules = $query->with(['route.destination', 'route.sourceDestination', 'bus'])
                          ->orderBy('departure_time', 'asc')
                          ->get();

        if ($request->ajax()) {
            return view('customer.schedules.partials.results', compact('schedules', 'travelDate'));
        }

        return view('customer.schedules.index', compact('schedules', 'destinations', 'travelDate', 'busTypes'));
    }
}
