<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Destination;

class ScheduleController extends Controller
{
    // GET /api/destinations
    public function getDestinations()
    {
        return response()->json(Destination::select('code', 'city_name')->get());
    }

    // GET /api/trips/search?from=JKT&to=BDG&date=2025-12-13
    public function search(Request $request)
    {
        // Validation
        $request->validate([
            'from' => 'nullable|string',
            'to' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        // Query the VIEW directly for speed
        $query = DB::table('view_available_trips');

        if ($request->from) {
            $query->where('source', 'LIKE', "%{$request->from}%");
        }
        if ($request->to) {
            $query->where('destination_code', $request->to);
        }
        if ($request->date) {
            // Compare just the date part of the timestamp
            $query->whereDate('departure_time', $request->date);
        }

        $trips = $query->orderBy('departure_time', 'asc')->get();

        return response()->json([
            'count' => $trips->count(),
            'data' => $trips
        ]);
    }
}