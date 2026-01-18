<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;
use App\Traits\ApiSearchable;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    use ApiSearchable;

    public function index(Request $request)
    {
        // \Illuminate\Support\Facades\Gate::authorize('manage-routes'); // Allow all admins to view
        $query = Route::with('destination');

        $searchable = [
            'id' => 'like',
            'source' => 'like',
            'destination_code' => 'like',
            'destination.city_name' => 'like'
        ];

        $sortable = ['id', 'source', 'destination_code', 'distance', 'estimated_duration', 'created_at'];

        $routes = $this->applyApiParams($query, $request, $searchable, $sortable, ['field' => 'id', 'order' => 'asc']);

        return response()->json($routes);
    }

    public function show($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-routes');
        $route = Route::with('destination')->findOrFail($id);
        return response()->json($route);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-routes');
        $request->validate([
            'source' => 'required|string|max:255',
            'destination_code' => 'required|exists:destinations,code',
            'distance' => 'nullable|integer|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            DB::statement("CALL sp_manage_route('CREATE', NULL, ?, ?, ?, ?)", [
                $request->source,
                $request->destination_code,
                $request->distance ?? 0,
                $request->estimated_duration ?? 0
            ]);
        });

        // Return latest compatible route as approximation or success status
        return response()->json(['message' => 'Route created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-routes');
        $route = Route::findOrFail($id);

        $request->validate([
            'source' => 'required|string|max:255',
            'destination_code' => 'required|exists:destinations,code',
            'distance' => 'nullable|integer|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($route, $request) {
            DB::statement("CALL sp_manage_route('UPDATE', ?, ?, ?, ?, ?)", [
                $route->id,
                $request->source,
                $request->destination_code,
                $request->distance ?? 0,
                $request->estimated_duration ?? 0
            ]);
        });

        return response()->json($route->fresh('destination'));
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-routes');
        $route = Route::findOrFail($id);
        $route->delete(); // Or Call SP if strictly required, but standard delete is usually fine unless complex constraints. AdminController used ->delete().
        return response()->json(null, 204);
    }
}
