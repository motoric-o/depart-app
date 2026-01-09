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

        $users = $query->paginate(10)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
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
        ]);

        $customerType = AccountType::where('name', 'Customer')->firstOrFail();

        Account::create([
            'account_type_id' => $customerType->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'password_hash' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users')->with('success', 'Customer created successfully.');
    }

    public function editUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if ($user->accountType->name !== 'Customer') {
            return redirect()->route('admin.users')->with('error', 'You can only edit Customer accounts.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if ($user->accountType->name !== 'Customer') {
            return redirect()->route('admin.users')->with('error', 'You can only edit Customer accounts.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:accounts,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
        ]);
        
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $user->update(['password_hash' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users')->with('success', 'Customer updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if ($user->accountType->name !== 'Customer') {
            return redirect()->route('admin.users')->with('error', 'You can only delete Customer accounts.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Customer deleted successfully.');
    }

    // --- BUSES MANAGEMENT ---

    public function buses()
    {
        $buses = Bus::paginate(10);
        return view('admin.buses.index', compact('buses'));
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
            'quota' => 'required|integer|min:1',
            'seat_rows' => 'required|integer|min:1',
            'seat_columns' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        Bus::create($request->all());

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
            'quota' => 'required|integer|min:1',
            'seat_rows' => 'required|integer|min:1',
            'seat_columns' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $bus->update($request->all());

        return redirect()->route('admin.buses')->with('success', 'Bus updated successfully.');
    }

    public function deleteBus($id)
    {
        $bus = Bus::findOrFail($id);
        $bus->delete();

        return redirect()->route('admin.buses')->with('success', 'Bus deleted successfully.');
    }

    // --- ROUTES MANAGEMENT ---

    public function routes()
    {
        $routes = BusRoute::with('destination')->paginate(10);
        return view('admin.routes.index', compact('routes'));
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

        BusRoute::create($request->all());

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

        $route->update($request->all());

        return redirect()->route('admin.routes')->with('success', 'Route updated successfully.');
    }

    public function deleteRoute($id)
    {
        $route = BusRoute::findOrFail($id);
        $route->delete();

        return redirect()->route('admin.routes')->with('success', 'Route deleted successfully.');
    }

    // --- SCHEDULES MANAGEMENT ---

    public function schedules()
    {
        $schedules = Schedule::with(['route.destination', 'bus'])->orderBy('departure_time', 'desc')->paginate(10);
        return view('admin.schedules.index', compact('schedules'));
    }

    public function createSchedule()
    {
        $routes = BusRoute::with('destination')->get();
        $buses = Bus::all();
        return view('admin.schedules.create', compact('routes', 'buses'));
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'bus_id' => 'required|exists:buses,id',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price_per_seat' => 'required|numeric|min:0',
        ]);

        Schedule::create($request->all());

        return redirect()->route('admin.schedules')->with('success', 'Schedule created successfully.');
    }

    public function editSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $routes = BusRoute::with('destination')->get();
        $buses = Bus::all();
        return view('admin.schedules.edit', compact('schedule', 'routes', 'buses'));
    }

    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'bus_id' => 'required|exists:buses,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price_per_seat' => 'required|numeric|min:0',
            'status' => 'required|string|in:Scheduled,Delayed,Cancelled,Completed',
        ]);

        $schedule->update($request->all());

        return redirect()->route('admin.schedules')->with('success', 'Schedule updated successfully.');
    }

    public function deleteSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.schedules')->with('success', 'Schedule deleted successfully.');
    }
}
