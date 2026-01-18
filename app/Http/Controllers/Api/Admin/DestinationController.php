<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Destination;
use Illuminate\Support\Facades\DB;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Destination::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('city_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        if ($request->filled('sort_by')) {
            $query->orderBy($request->sort_by, $request->input('sort_order', 'asc'));
        } else {
            $query->orderBy('city_name', 'asc');
        }

        $destinations = $query->paginate(10);

        return response()->json($destinations);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($code)
    {
        try {
            DB::transaction(function () use ($code) {
                // Using stored procedure for safe delete
                DB::statement("CALL sp_delete_destination(?)", [$code]);
            });
            return response()->json(['success' => true, 'message' => 'Destination deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400); // 400 Bad Request if FK constraint
        }
    }
}
