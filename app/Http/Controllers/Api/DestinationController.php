<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Route;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    // API: GET /api/locations
    public function index()
    {
        // 1. Get all possible "Source" cities (From the Routes table)
        // We use distinct() because 'Jakarta' might appear in 50 different routes.
        $sources = Route::select('source')->distinct()->pluck('source');

        // 2. Get all possible "Destination" cities (From the Destinations table)
        $destinations = Destination::select('city_name', 'code')->get();

        return response()->json([
            'sources' => $sources,
            'destinations' => $destinations
        ]);
    }
}