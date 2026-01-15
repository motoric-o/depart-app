<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bus;

use App\Traits\ApiSearchable;

class BusController extends Controller
{
    use ApiSearchable;

    public function index(Request $request)
    {
        // \Illuminate\Support\Facades\Gate::authorize('manage-buses'); // Allow all admins to view
        $query = Bus::query();

        $searchable = [
            'bus_number' => 'like',
            'bus_type' => 'like'
        ];

        $sortable = ['bus_number', 'bus_type', 'capacity', 'created_at'];

        $buses = $this->applyApiParams($query, $request, $searchable, $sortable, ['field' => 'bus_number', 'order' => 'asc']);

        return response()->json($buses);
    }

    public function show($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-buses');
        $bus = Bus::findOrFail($id);
        return response()->json($bus);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-buses');
        $validated = $request->validate([
            'bus_number' => 'required|string|unique:buses,bus_number',
            'bus_type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'seat_rows' => 'required|integer|min:1',
            'seat_columns' => 'required|integer|min:1',
        ]);

        $bus = Bus::create($validated);
        return response()->json($bus, 201);
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-buses');
        $bus = Bus::findOrFail($id);

        $validated = $request->validate([
            'bus_number' => 'required|string|unique:buses,bus_number,' . $bus->id,
            'bus_type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'seat_rows' => 'required|integer|min:1',
            'seat_columns' => 'required|integer|min:1',
        ]);

        $bus->update($validated);
        return response()->json($bus);
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-buses');
        $bus = Bus::findOrFail($id);
        $bus->delete();
        return response()->json(null, 204);
    }
}

