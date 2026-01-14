<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Bus;
use App\Models\Route as BusRoute; // Alias to avoid conflict with Facade
use App\Models\AccountType;
use App\Models\Destination;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // --- USERS MANAGEMENT ---

    public function users(\Illuminate\Http\Request $request)
    {
        $query = Account::with('accountType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('accountType', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Sorting
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        $allowed_sorts = ['id', 'first_name', 'email', 'created_at'];
        
        if (in_array($sort_by, $allowed_sorts)) {
            $query->orderBy($sort_by, $sort_order);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(10)->withQueryString();
        return view('admin.users.index', compact('users', 'sort_by', 'sort_order'));
    }

    public function createUser()
    {
        $roles = AccountType::whereIn('name', ['Customer', 'Driver'])->get();
        return view('admin.users.create', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:accounts',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        $role = AccountType::findOrFail($request->account_type_id);
        if (!in_array($role->name, ['Customer', 'Driver'])) {
             return back()->withErrors(['account_type_id' => 'Invalid role selected.']);
        }

        \Illuminate\Support\Facades\DB::statement("CALL sp_create_customer(?, ?, ?, ?, ?, ?, ?)", [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->birthdate,
            Hash::make($request->password),
            $role->id
        ]);

        return redirect()->route('admin.users')->with('success', 'Customer created successfully.');
    }

    public function editUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if (!in_array($user->accountType->name, ['Customer', 'Driver'])) {
            return redirect()->route('admin.users')->with('error', 'You can only edit Customer or Driver accounts.');
        }

        $roles = AccountType::whereIn('name', ['Customer', 'Driver'])->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if (!in_array($user->accountType->name, ['Customer', 'Driver'])) {
            return redirect()->route('admin.users')->with('error', 'You can only edit Customer or Driver accounts.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:accounts,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        $role = AccountType::findOrFail($request->account_type_id);
        if (!in_array($role->name, ['Customer', 'Driver'])) {
             return back()->withErrors(['account_type_id' => 'Invalid role selected.']);
        }

        $passwordHash = $user->password_hash;
        if ($request->filled('password')) {
             $request->validate(['password' => 'string|min:8|confirmed']);
             $passwordHash = Hash::make($request->password);
        }

        \Illuminate\Support\Facades\DB::statement("CALL sp_update_customer(?, ?, ?, ?, ?, ?, ?)", [
            $user->id,
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->birthdate,
            $role->id
        ]);
        
        // Handling password update logic inside SP would be cleaner but current SP doesn't support conditional password update easily without passing all fields.
        // Actually sp_update_customer doesn't take password! I missed that in the SP definition!
        // In the migration, sp_update_customer inputs: p_id, p_first, p_last, p_email, p_phone, p_birthdate, p_account_type_id.
        // It DOES NOT take password.
        // So I need to update password separately via model or modify SP?
        // Since the user asked for SPs, I should probably use SPs. But if I modify SP now, I need to migrate again.
        // I'll stick to model for password or just separate query for now?
        // "Make as many as you can".
        // I'll update the password separately if changed, using raw SQL or Model. Model is fine for "parts not covered by SP".
        // BUT, better to just update the password via Model for now to avoid re-migration loop delay.
        
        if ($request->filled('password')) {
             $user->update(['password_hash' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if (!in_array($user->accountType->name, ['Customer', 'Driver'])) {
            return redirect()->route('admin.users')->with('error', 'You can only delete Customer or Driver accounts.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    // --- BUSES MANAGEMENT ---

    public function buses(Request $request)
    {
        $query = Bus::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bus_number', 'like', "%{$search}%")
                  ->orWhere('bus_type', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort_by = $request->get('sort_by', 'bus_number');
        $sort_order = $request->get('sort_order', 'asc');
        $allowed_sorts = ['bus_number', 'bus_type', 'capacity'];
        
        if (in_array($sort_by, $allowed_sorts)) {
            $query->orderBy($sort_by, $sort_order);
        } else {
             $query->orderBy('bus_number', 'asc');
        }

        $buses = $query->paginate(10)->withQueryString();
        return view('admin.buses.index', compact('buses', 'sort_by', 'sort_order'));
    }

    public function createBus()
    {
        return view('admin.buses.create');
    }

    public function storeBus(Request $request)
    {
        $request->validate([
            'bus_number' => 'required|string|unique:buses|max:50',
            'bus_type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'seat_rows' => 'required|integer|min:1',
            'seat_columns' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::statement("CALL sp_manage_bus('CREATE', NULL, ?, ?, ?, ?, ?, ?, ?)", [
            $request->bus_number,
            $request->bus_type,
            $request->capacity,
            $request->seat_rows,
            $request->seat_columns,
            $request->remarks
        ]);

        return redirect()->route('admin.buses')->with('success', 'Bus created successfully.');
    }

    public function editBus($id)
    {
        $bus = Bus::findOrFail($id);
        return view('admin.buses.edit', compact('bus'));
    }

    public function updateBus(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);

        $request->validate([
            'bus_number' => 'required|string|max:50|unique:buses,bus_number,' . $bus->id,
            'bus_type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'seat_rows' => 'required|integer|min:1',
            'seat_columns' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::statement("CALL sp_manage_bus('UPDATE', ?, ?, ?, ?, ?, ?, ?, ?)", [
            $bus->id,
            $request->bus_number,
            $request->bus_type,
            $request->capacity,
            $request->seat_rows,
            $request->seat_columns,
            $request->remarks
        ]);

        return redirect()->route('admin.buses')->with('success', 'Bus updated successfully.');
    }

    public function deleteBus($id)
    {
        // Deletion procedure not implemented in plan, using Eloquent
        $bus = Bus::findOrFail($id);
        $bus->delete();

        return redirect()->route('admin.buses')->with('success', 'Bus deleted successfully.');
    }

    // --- ROUTES MANAGEMENT ---

    public function routes(Request $request)
    {
        $query = BusRoute::with(['sourceDestination', 'destination']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('destination', function($subQ) use ($search) {
                      $subQ->where('city_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort_by = $request->get('sort_by', 'id');
        $sort_order = $request->get('sort_order', 'asc');
        $allowed_sorts = ['id', 'source', 'destination_code', 'distance', 'estimated_duration'];
        
        if (in_array($sort_by, $allowed_sorts)) {
            $query->orderBy($sort_by, $sort_order);
        } else {
             $query->orderBy('id', 'asc');
        }

        $routes = $query->paginate(10)->withQueryString();
        return view('admin.routes.index', compact('routes', 'sort_by', 'sort_order'));
    }

    public function createRoute()
    {
        $destinations = Destination::all();
        return view('admin.routes.create', compact('destinations'));
    }

    public function storeRoute(Request $request)
    {
        $request->validate([
            'source' => 'required|string|max:255',
            'destination_code' => 'required|exists:destinations,code',
            'distance' => 'nullable|integer|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::statement("CALL sp_manage_route('CREATE', NULL, ?, ?, ?, ?)", [
            $request->source,
            $request->destination_code,
            $request->distance ?? 0,
            $request->estimated_duration ?? 0
        ]);

        return redirect()->route('admin.routes')->with('success', 'Route created successfully.');
    }

    public function editRoute($id)
    {
        $route = BusRoute::findOrFail($id);
        $destinations = Destination::all();
        return view('admin.routes.edit', compact('route', 'destinations'));
    }

    public function updateRoute(Request $request, $id)
    {
        $route = BusRoute::findOrFail($id);

        $request->validate([
            'source' => 'required|string|max:255',
            'destination_code' => 'required|exists:destinations,code',
            'distance' => 'nullable|integer|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::statement("CALL sp_manage_route('UPDATE', ?, ?, ?, ?, ?)", [
            $route->id,
            $request->source,
            $request->destination_code,
            $request->distance ?? 0,
            $request->estimated_duration ?? 0
        ]);

        return redirect()->route('admin.routes')->with('success', 'Route updated successfully.');
    }

    public function deleteRoute($id)
    {
        $route = BusRoute::findOrFail($id);
        $route->delete();

        return redirect()->route('admin.routes')->with('success', 'Route deleted successfully.');
    }

    // --- SCHEDULES MANAGEMENT ---

    public function schedules(Request $request)
    {
        $query = Schedule::with(['route.destination', 'bus']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search by Schedule ID
                $q->where('id', 'like', "%{$search}%")
                  // Search by Bus Number via relationship
                  ->orWhereHas('bus', function($subQ) use ($search) {
                      $subQ->where('bus_number', 'like', "%{$search}%");
                  })
                  // Search by Route Source or Destination City
                  ->orWhereHas('route', function($subQ) use ($search) {
                      $subQ->where('source', 'like', "%{$search}%")
                           ->orWhereHas('destination', function($subSubQ) use ($search) {
                               $subSubQ->where('city_name', 'like', "%{$search}%");
                           });
                  });
            });
        }

        // Sorting
        $sort_by = $request->get('sort_by', 'departure_time');
        $sort_order = $request->get('sort_order', 'desc');
        $allowed_sorts = ['id', 'route_id', 'bus_id', 'departure_time', 'arrival_time', 'price_per_seat', 'remarks'];
        
        if (in_array($sort_by, $allowed_sorts)) {
            $query->orderBy($sort_by, $sort_order);
        } else {
             $query->orderBy('departure_time', 'desc');
        }

        $schedules = $query->paginate(10)->withQueryString();
        return view('admin.schedules.index', compact('schedules', 'sort_by', 'sort_order'));
    }

    public function createSchedule()
    {
        $routes = BusRoute::with('destination')->get();
        $buses = Bus::all();
        $drivers = Account::whereHas('accountType', function($q) {
            $q->where('name', 'Driver');
        })->get();
        return view('admin.schedules.create', compact('routes', 'buses', 'drivers'));
    }

    public function storeSchedule(Request $request)
    {
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
            \Illuminate\Support\Facades\DB::statement("CALL sp_create_schedule(?, ?, ?, ?, ?, ?)", [
                $request->route_id,
                $request->bus_id,
                $request->departure_time,
                $request->arrival_time,
                $request->price_per_seat,
                $request->quota
            ]);
            
            // Fetch the last created schedule for this bus/time (approximate) or use ID if SP returned it.
            // Stored procedure usually doesn't return ID easily in Laravel statement call.
            // I will find the schedule I just created.
            $schedule = Schedule::where('bus_id', $request->bus_id)
                        ->where('departure_time', $request->departure_time)
                        ->latest()
                        ->first();
                        
            if ($schedule) {
                $schedule->driver_id = $request->driver_id;
                $schedule->save();
            }

        } catch (\Illuminate\Database\QueryException $e) {
             if (str_contains($e->getMessage(), 'Bus is already scheduled')) {
                 return back()->withInput()->withErrors(['bus_id' => 'Bus is already scheduled for this time range.']);
             }
             throw $e;
        }

        return redirect()->route('admin.schedules')->with('success', 'Schedule created successfully.');
    }

    public function editSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $routes = BusRoute::with('destination')->get();
        $buses = Bus::all();
        $drivers = Account::whereHas('accountType', function($q) {
            $q->where('name', 'Driver');
        })->get();
        return view('admin.schedules.edit', compact('schedule', 'routes', 'buses', 'drivers'));
    }

    public function updateSchedule(Request $request, $id)
    {
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
        
        // Note: Currently we only have sp_update_schedule_status. 
        // Logic: If user changes times/bus, we should probably recreate or update properly. 
        // For now, to satisfy "use stored procedures", I will use sp_update_schedule_status for status change, 
        // and Eloquent for other fields if changed? 
        // OR better: Just use Eloquent for "edit details" and SP for "Status Update".
        // BUT the user asked to replace logic.
        // Assuming the most common operation here is Status Update or simple edit.
        // I will stick to Eloquent for the full "Edit" to avoid data loss on non-status fields,
        // UNLESS I update the SP to handle all fields.
        // Re-reading implementation plan: "Replaces: AdminController@updateSchedule".
        // But my SP `sp_update_schedule_status` only takes (id, status).
        // I'll assume for this specific update, I will ONLY use SP for the STATUS part, 
        // and keep Eloquent for the rest to be safe, OR I should have made a better SP.
        // To be safe and functional: I'll update other fields via Eloquent, then call SP for status/timestamp update.
        
        // Determine Remarks
        // If remarks in form is null or not present, it might be due to UI logic.
        // We expect 'remarks_select' and optionally 'remarks_other' from the form if we adhere to the plan.
        // Let's assume the form sends 'remarks' directly via JS logic OR we handle it here.
        // User asked for "add another option 'other' that lets us input a text".
        // Use logic: if request->remarks_select == 'Other', use request->remarks_other -> else use request->remarks_select.
        
        $remarks = $request->input('remarks'); // Default if processed by JS
        
        if ($request->has('remarks_select')) {
             if ($request->remarks_select === 'Other') {
                 $remarks = $request->remarks_other;
             } else {
                 $remarks = $request->remarks_select;
             }
        }

        $schedule->fill($request->except(['remarks', 'remarks_select', 'remarks_other'])); // Update details
        if ($schedule->isDirty()) {
             $schedule->save();
        }
        
        // Use SP for Remarks (and timestamp update included in SP)
        \Illuminate\Support\Facades\DB::statement("CALL sp_update_schedule_remarks(?, ?)", [
            $schedule->id,
            $remarks
        ]);   

        return redirect()->route('admin.schedules')->with('success', 'Schedule updated successfully.');
    }

    public function deleteSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);

        // Delete dependencies Manually due to missing CASCADE
        \Illuminate\Support\Facades\DB::transaction(function () use ($schedule) {
            // 1. Delete ScheduleDetails
            $schedule->scheduleDetails()->delete();

            // 2. Get Bookings
            $bookings = \App\Models\Booking::where('schedule_id', $schedule->id)->get();
            
            foreach ($bookings as $booking) {
                // Delete Tickets
                \App\Models\Ticket::where('booking_id', $booking->id)->delete();
                
                // Transactions: Set ticket_id to null or delete? 
                // FK on transactions.booking_id doesn't cascade either?
                // Migration 2025_12_13_000010_create_transactions_table.php: 
                // $table->foreign('booking_id')->references('id')->on('bookings'); (No Cascade)
                // We should probably delete the transaction if it's strictly linked to this booking.
                \App\Models\Transaction::where('booking_id', $booking->id)->delete();
                
                $booking->delete();
            }

            $schedule->delete();
        });

        return redirect()->route('admin.schedules')->with('success', 'Schedule deleted successfully.');
    }

    public function scheduleDetails($id)
    {
        $schedule = Schedule::findOrFail($id);
        return view('admin.schedules.details', compact('schedule'));
    }
}
