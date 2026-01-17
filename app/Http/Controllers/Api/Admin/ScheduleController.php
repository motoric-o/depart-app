<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;

use App\Traits\ApiSearchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    use ApiSearchable;

    public function index(Request $request)
    {
        // \Illuminate\Support\Facades\Gate::authorize('manage-schedules'); // Allow all admins to view
        $query = Schedule::with(['route.destination', 'bus', 'driver']);

        $searchable = [
            'id' => 'like',
            'bus.bus_number' => 'like',
            'driver.first_name' => 'like',
            'driver.last_name' => 'like',
            'route.source' => 'like',
            'route.destination.city_name' => 'like'
        ];

        $sortable = ['id', 'route_id', 'bus_id', 'driver_id', 'departure_time', 'arrival_time', 'price_per_seat', 'remarks'];

        // Filter: Incomplete
        if ($request->get('filter_category') === 'incomplete') {
            $query->where(function($q) {
                $q->whereNull('bus_id')
                  ->orWhereNull('driver_id')
                  ->orWhereNull('route_id')
                  ->orWhere('remarks', 'like', '%Canceled%')
                  ->orWhere('remarks', 'like', '%Pending%');
            });
        }

        // Custom Sort: Problematic items on top
        $query->orderByRaw("CASE WHEN bus_id IS NULL OR driver_id IS NULL OR remarks LIKE '%Canceled%' OR remarks LIKE '%Pending%' THEN 0 ELSE 1 END");

        $schedules = $this->applyApiParams($query, $request, $searchable, $sortable, ['field' => 'departure_time', 'order' => 'desc']);

        return response()->json($schedules);
    }

    public function show($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-schedules');
        $schedule = Schedule::with(['route.destination', 'bus', 'driver'])->findOrFail($id);
        return response()->json($schedule);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-schedules');
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'bus_id' => 'required|exists:buses,id',
            'driver_id' => 'required|exists:accounts,id',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price_per_seat' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
        ]);

        try {
            DB::statement("CALL sp_create_schedule(?, ?, ?, ?, ?, ?, ?)", [
                $request->route_id,
                $request->bus_id,
                $request->driver_id,
                $request->departure_time,
                $request->arrival_time,
                $request->price_per_seat,
                $request->quota
            ]);

            // Fetch the latest created schedule to return it (approximation as SP doesn't return ID directly usually)
            // Or just return success message. API standard usually returns object.
            // For now, let's return a success message or the latest schedule for this bus.
            $schedule = Schedule::latest()->first(); 
            
            return response()->json($schedule, 201);

        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'Bus is already scheduled')) {
                 throw ValidationException::withMessages(['bus_id' => 'Bus is already scheduled for this time range.']);
            }
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-schedules');
        $schedule = Schedule::findOrFail($id);

        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'bus_id' => 'required|exists:buses,id',
            'driver_id' => 'required|exists:accounts,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price_per_seat' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $schedule->fill($request->except(['remarks']));
        if ($schedule->isDirty()) {
            $schedule->save();
        }

        // Use SP for Remarks
        $remarks = $request->input('remarks', $schedule->remarks);
        DB::statement("CALL sp_update_schedule_remarks(?, ?)", [
            $schedule->id,
            $remarks
        ]);
        
        return response()->json($schedule->fresh(['route.destination', 'bus', 'driver']));
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-schedules');
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return response()->json(null, 204);
    }
}

