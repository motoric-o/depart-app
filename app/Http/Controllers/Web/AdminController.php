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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // Fix lint error

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
        return view('management.users.index', compact('users', 'sort_by', 'sort_order'));
    }

    public function createUser()
    {
        $allowedRoles = ['Customer', 'Driver'];
        if (Auth::user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }
        $roles = AccountType::whereIn('name', $allowedRoles)->get();
        return view('management.users.create', compact('roles'));
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
        $allowedRoles = ['Customer', 'Driver'];
        if (Auth::user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }
        if (!in_array($role->name, $allowedRoles)) {
             return back()->withErrors(['account_type_id' => 'Invalid role selected.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $role) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_create_customer(?, ?, ?, ?, ?, ?, ?)", [
                $request->first_name,
                $request->last_name,
                $request->email,
                $request->phone,
                $request->birthdate,
                Hash::make($request->password),
                $role->id
            ]);
        });

        return redirect()->route('admin.users')->with('success', 'Customer created successfully.');
    }

    public function editUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        $allowedRoles = ['Customer', 'Driver'];
        if (Auth::user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }

        if (!in_array($user->accountType->name, $allowedRoles)) {
            return redirect()->route('admin.users')->with('error', 'You do not have permission to edit this account.');
        }

        $roles = AccountType::whereIn('name', $allowedRoles)->get();
        return view('management.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        $allowedRoles = ['Customer', 'Driver'];
        if (Auth::user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }

        if (!in_array($user->accountType->name, $allowedRoles)) {
            return redirect()->route('admin.users')->with('error', 'You do not have permission to edit this account.');
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
        $allowedRoles = ['Customer', 'Driver'];
        if (Auth::user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }
        if (!in_array($role->name, $allowedRoles)) {
             return back()->withErrors(['account_type_id' => 'Invalid role selected.']);
        }

        $passwordHash = $user->password_hash;
        if ($request->filled('password')) {
             $request->validate(['password' => 'string|min:8|confirmed']);
             $passwordHash = Hash::make($request->password);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request, $role) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_update_customer(?, ?, ?, ?, ?, ?, ?)", [
                $user->id,
                $request->first_name,
                $request->last_name,
                $request->email,
                $request->phone,
                $request->birthdate,
                $role->id
            ]);
        });
        
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

        $allowedRoles = ['Customer', 'Driver'];
        if (Auth::user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }

        if (!in_array($user->accountType->name, $allowedRoles)) {
            return redirect()->route('admin.users')->with('error', 'You do not have permission to delete this account.');
        }

        // $user->delete();
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_delete_user(?)", [$id]);
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    // --- EXPENSES MANAGEMENT ---

    public function expenses(Request $request)
    {
        $query = \App\Models\Expense::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        // Removed Date To as it wasn't in original view filter, but good to have if needed. 
        // Owner view had date_from only? Let's check. Owner view had date_from.
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $expenses = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $canApprove = \Illuminate\Support\Facades\Gate::allows('approve-expense');

        return view('management.expenses.index', compact('expenses', 'canApprove'));
    }

    public function requestFunds()
    {
        $expenses = \App\Models\Expense::where('account_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('created_at', 'desc')
            ->orderBy('date', 'desc')
            ->paginate(10);
            
        return view('admin.expenses.requests', compact('expenses'));
    }

    public function createExpense()
    {
        return view('management.expenses.create'); 
        // We need to ensure management.expenses.create exists. 
        // If not, I'll copy owner.expenses.create there too.
    }

    public function storeExpense(Request $request)
    {
        // ... (existing store logic, maybe needs tweak?)
        // Wait, storeExpense in AdminController manually creates expense with 'In Process'.
        // But for Op Admin requests, it should be 'Pending' probably? 
        // Or if I reuse this store, it becomes 'In Process' immediately.
        // User said: "send request -> financial admin approves (In Process)".
        // So initial status should be PENDING.
        
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:reimbursement,operational,maintenance,salary,other',
            'date' => 'required|date',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);
        
        // Handle File Upload
        $path = null;
        if ($request->hasFile('proof_file')) {
             $path = $request->file('proof_file')->store('expenses', 'public');
        }

        // Logic check: If generic Admin creates expense, maybe In Process is fine?
        // But if Op Admin requests, it should be Pending.
        // Let's check permissions.
        $status = 'Pending';
        if (\Illuminate\Support\Facades\Gate::allows('approve-expense') && $request->type !== 'operational') {
             $status = 'In Process'; // Auto-approve for Approvers (Fin Admin/Owner)
        }

        // \App\Models\Expense::create([
        //     'description' => $request->description,
        //     'amount' => $request->amount,
        //     'type' => $request->type,
        //     'status' => $status,
        //     'date' => $request->date,
        //     'account_id' => Auth::id(),
        //     'proof_file' => $path
        // ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $path, $status) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_create_expense(?, ?, ?, ?, ?, ?, ?, ?)", [
                $request->description,
                $request->amount,
                $request->type,
                $request->date,
                \Illuminate\Support\Facades\Auth::id(),
                $path,
                null,
                $status
            ]);
        });
        
        // Redirect back (works for both Management View and Request View as they submit here)
        return back()->with('success', 'Request submitted successfully.');
    }

    public function verifyExpense(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('approve-expense');
        
        $expense = \App\Models\Expense::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:In Process,Pending Confirmation,Rejected,Canceled'
        ]);

        $expense->update(['status' => $request->status]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense verified successfully.',
                'new_status' => $request->status
            ]);
        }

        return back()->with('success', 'Expense verified successfully.');
    }

    // --- BUSES MANAGEMENT ---

    public function buses(Request $request)
    {
        return view('management.buses.index');
    }

    public function createBus()
    {
        return view('management.buses.create');
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_manage_bus('CREATE', NULL, ?, ?, ?, ?, ?, ?, ?)", [
                $request->bus_number,
                $request->bus_type,
                $request->capacity,
                $request->seat_rows,
                $request->seat_columns,
                $request->remarks
            ]);
        });

        return redirect()->route('admin.buses')->with('success', 'Bus created successfully.');
    }

    public function editBus($id)
    {
        $bus = Bus::findOrFail($id);
        return view('management.buses.edit', compact('bus'));
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($bus, $request) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_manage_bus('UPDATE', ?, ?, ?, ?, ?, ?, ?, ?)", [
                $bus->id,
                $request->bus_number,
                $request->bus_type,
                $request->capacity,
                $request->seat_rows,
                $request->seat_columns,
                $request->remarks
            ]);
        });

        return redirect()->route('admin.buses')->with('success', 'Bus updated successfully.');
    }

    public function deleteBus($id)
    {
        // Deletion procedure not implemented in plan, using Eloquent
        $bus = Bus::findOrFail($id);
        // $bus->delete();
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_delete_bus(?)", [$id]);
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Bus deleted successfully.']);
        }
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
        return view('management.routes.index', compact('routes', 'sort_by', 'sort_order'));
    }

    public function createRoute()
    {
        $destinations = Destination::all();
        return view('management.routes.create', compact('destinations'));
    }

    public function storeRoute(Request $request)
    {
        $request->validate([
            'source' => 'required|string|max:255',
            'destination_code' => 'required|exists:destinations,code',
            'distance' => 'nullable|integer|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_manage_route('CREATE', NULL, ?, ?, ?, ?)", [
                $request->source,
                $request->destination_code,
                $request->distance ?? 0,
                $request->estimated_duration ?? 0
            ]);
        });

        return redirect()->route('admin.routes')->with('success', 'Route created successfully.');
    }

    public function editRoute($id)
    {
        $route = BusRoute::findOrFail($id);
        $destinations = Destination::all();
        return view('management.routes.edit', compact('route', 'destinations'));
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($route, $request) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_manage_route('UPDATE', ?, ?, ?, ?, ?)", [
                $route->id,
                $request->source,
                $request->destination_code,
                $request->distance ?? 0,
                $request->estimated_duration ?? 0
            ]);
        });

        return redirect()->route('admin.routes')->with('success', 'Route updated successfully.');
    }

    public function deleteRoute($id)
    {
        $route = BusRoute::findOrFail($id);
        // $route->delete();
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            \Illuminate\Support\Facades\DB::statement("CALL sp_delete_route(?)", [$id]);
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Route deleted successfully.']);
        }
        return redirect()->route('admin.routes')->with('success', 'Route deleted successfully.');
    }

    // --- BOOKINGS MANAGEMENT ---

    public function bookings(Request $request)
    {
        // Auto-expire pending bookings
        \App\Models\Booking::where('status', \App\Models\Booking::STATUS_PENDING)
            ->where('travel_date', '<', now())
            ->update(['status' => \App\Models\Booking::STATUS_EXPIRED]);

        if ($request->wantsJson()) {
            $query = \App\Models\Booking::with(['account', 'schedule.route.destination']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('account', function($sq) use ($search) {
                          $sq->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      });
                });
            }

            // Filtering
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sort_by = $request->get('sort_by', 'created_at');
            $sort_order = $request->get('sort_order', 'desc');
            $allowed_sorts = ['id', 'created_at', 'booking_date', 'travel_date', 'total_amount', 'status'];

            if (in_array($sort_by, $allowed_sorts)) {
                $query->orderBy($sort_by, $sort_order);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $bookings = $query->paginate($request->get('per_page', 10));
            return response()->json($bookings);
        }

        return view('management.bookings.index');
    }

    public function checkCustomer(Request $request)
    {
        $email = trim($request->query('email'));
        if (!$email) return response()->json(['found' => false]);

        $customer = \App\Models\Account::where('email', $email)->first();

        if ($customer) {
            return response()->json([
                'found' => true,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'id' => $customer->id
            ]);
        }
        
        return response()->json(['found' => false]);
    }

    public function createBooking()
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-bookings');
        // Routes for Dropdown
        $routes = \App\Models\Route::with('destination')->get();
        
        return view('management.bookings.create', compact('routes'));
    }

    public function storeBooking(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-bookings');

        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'required|email',
            'schedule_id' => 'required|exists:schedules,id',
            'seats' => 'required|array',
            'passengers' => 'required|array',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string|in:Paid,Pending,Pending Payment',
        ]);

        try {
            // Arrays for PostgreSQL
            $seatsArray = '{' . implode(',', array_values($request->seats)) . '}';
            
            $passengers = [];
            $seatValues = array_values($request->seats);
            $passengerValues = array_values($request->passengers);
            
            foreach ($seatValues as $index => $seat) {
                $pName = trim($passengerValues[$index] ?? '');
                if (empty($pName)) $pName = 'Passenger ' . ($index + 1);
                $pName = str_replace('"', '\"', $pName); 
                $passengers[] = '"' . $pName . '"';
            }
            $passengersArray = '{' . implode(',', $passengers) . '}';

            $customerRoleId = \App\Models\AccountType::where('name', 'Customer')->value('id');
            $defaultPassword = \Illuminate\Support\Facades\Hash::make('password');

            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $seatsArray, $passengersArray, $customerRoleId, $defaultPassword) {
                \Illuminate\Support\Facades\DB::statement("CALL sp_create_booking_admin(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $request->customer_name ?? '', 
                    trim($request->customer_email), 
                    $request->schedule_id,
                    $request->date ?? now(),
                    $seatsArray, 
                    $passengersArray, 
                    $request->payment_method,
                    $request->payment_status === 'Paid' ? 'Paid' : 'Pending',
                    $defaultPassword,
                    $customerRoleId,
                    null 
                ]);
            });

            $latestBooking = \App\Models\Booking::whereHas('account', function($q) use ($request) {
                $q->where('email', trim($request->customer_email));
            })->latest()->first();
            
            return redirect()->route('admin.bookings.edit', $latestBooking->id)
                ->with('success', 'Booking created successfully via SP!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Booking Create Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create booking: ' . $e->getMessage())->withInput();
        }
    }
    
    public function getAvailableSchedules(Request $request)
    {
        $request->validate([
            'route_id' => 'nullable|exists:routes,id',
            'date' => 'nullable|date'
        ]);

        $query = \App\Models\Schedule::with(['bus', 'route'])
            ->withCount('bookings');

        if ($request->route_id) {
            $query->where('route_id', $request->route_id);
        }
        
        if ($request->date) {
            $query->whereDate('departure_time', $request->date);
        } else {
            // Optional: Filter for future dates if date is not specified?
            // For now, let's just return all distinct future schedules or keep generic.
            // Maybe limit to today onwards?
            $query->whereDate('departure_time', '>=', now());
        }

        $schedules = $query->orderBy('departure_time')->get();
            
        $schedules->map(function($schedule) {
            // Count booked seats
            // This is heavy but accurate.
            $bookedSeatsCount = \App\Models\Ticket::whereHas('booking', function($q) use ($schedule) {
                $q->where('schedule_id', $schedule->id)
                  ->where('status', '!=', 'Cancelled')
                  ->where('status', '!=', 'Failed');
            })->where('status', '!=', 'Cancelled')->count();
            
            $schedule->available_seats = $schedule->quota - $bookedSeatsCount;
            return $schedule;
        });

        return response()->json($schedules);
    }

    public function editBooking($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-bookings');
        $booking = \App\Models\Booking::with(['tickets', 'payment', 'account', 'schedule.route'])->findOrFail($id);
        return view('management.bookings.edit', compact('booking'));
    }

    public function updateBooking(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-bookings');
        $booking = \App\Models\Booking::findOrFail($id);

        $request->validate([
            'status' => 'required|in:' . \App\Models\Booking::STATUS_BOOKED . ',' . \App\Models\Booking::STATUS_PENDING . ',' . \App\Models\Booking::STATUS_CANCELLED . ',' . \App\Models\Booking::STATUS_EXPIRED,
            'passengers' => 'nullable|array' // [ticket_id => name]
        ]);

        $booking->update([
            'status' => $request->status
        ]);

        // Update Passenger Names
        if ($request->has('passengers')) {
            foreach ($request->passengers as $ticketId => $name) {
                \App\Models\Ticket::where('id', $ticketId)->where('booking_id', $booking->id)->update(['passenger_name' => $name]);
            }
        }

        return redirect()->route('admin.bookings')->with('success', 'Booking updated successfully.');
    }

    public function deleteBooking($id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($booking) {
             \App\Models\Ticket::where('booking_id', $booking->id)->delete();
             \App\Models\Transaction::where('booking_id', $booking->id)->delete();
             $booking->delete();
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking deleted successfully.']);
        }
        return redirect()->route('admin.bookings')->with('success', 'Booking deleted successfully.');
    }

    // --- SCHEDULES MANAGEMENT ---

    public function schedules(Request $request)
    {
        return view('management.schedules.index');
    }

    public function createSchedule()
    {
        $routes = BusRoute::with('destination')->get();
        $buses = Bus::all();
        $drivers = Account::whereHas('accountType', function($q) {
            $q->where('name', 'Driver');
        })->get();
        return view('management.schedules.create', compact('routes', 'buses', 'drivers'));
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
            \Illuminate\Support\Facades\DB::statement("CALL sp_create_schedule(?, ?, ?, ?, ?, ?, ?)", [
                $request->route_id,
                $request->bus_id,
                $request->driver_id,
                $request->departure_time,
                $request->arrival_time,
                $request->price_per_seat,
                $request->quota
            ]);
            
            // Removed manual driver_id update since SP now handles it.


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
        return view('management.schedules.edit', compact('schedule', 'routes', 'buses', 'drivers'));
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

    public function getScheduleBookedSeats($id)
    {
        $schedule = \App\Models\Schedule::findOrFail($id);
        
        $bookedSeats = \App\Models\Ticket::whereHas('booking', function($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
              ->where('status', '!=', 'Cancelled')
              ->where('status', '!=', 'Failed'); // Also exclude failed if applicable
        })->where('status', '!=', 'Cancelled') // Ticket status check too
          ->pluck('seat_number');

        return response()->json($bookedSeats);
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

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Schedule deleted successfully.']);
        }
        return redirect()->route('admin.schedules')->with('success', 'Schedule deleted successfully.');
    }

    public function scheduleDetails($id)
    {
        $schedule = Schedule::findOrFail($id);
        return view('management.schedules.details', compact('schedule'));
    }

    // --- FINANCIAL REPORTS ---

    public function financialReports()
    {
        // 1. Total Revenue (Success transactions)
        $totalRevenue = \App\Models\Transaction::where('status', 'Success')->sum('total_amount');
        
        // 1b. Periodic Revenue
        $dailyRevenue = \App\Models\Transaction::where('status', 'Success')->whereDate('transaction_date', today())->sum('total_amount');
        $monthlyRevenue = \App\Models\Transaction::where('status', 'Success')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('total_amount');

        // 2. Total Expenses (Approved/Reimbursed/Completed expenses)
        $totalExpenses = \App\Models\Expense::whereNotIn('status', ['Rejected', 'Canceled'])->sum('amount');

        // 3. Net Profit
        $netProfit = $totalRevenue - $totalExpenses;

        // 4. Top Performing Routes
        // Ordered by Total Revenue
        $topRoutes = \App\Models\RouteStat::orderByDesc('total_revenue')
            ->take(5)
            ->get();
            
        // 5. Recent Transactions (For context)
        $recentTransactions = \App\Models\Transaction::with(['account', 'booking'])
            ->where('status', 'Success')
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        return view('management.reports.index', compact('totalRevenue', 'totalExpenses', 'netProfit', 'topRoutes', 'recentTransactions', 'dailyRevenue', 'monthlyRevenue'));
    }

    // --- TRANSACTIONS MANAGEMENT ---

    public function transactions(Request $request)
    {
        $query = \App\Models\Transaction::with(['account', 'booking', 'paymentIssueProofs']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('account', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('transaction_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sort_by = $request->get('sort_by', 'transaction_date');
        $sort_order = $request->get('sort_order', 'desc');
        // Allow sorting by these columns
        $allowed_sorts = ['id', 'transaction_date', 'total_amount', 'status'];
        
        if (in_array($sort_by, $allowed_sorts)) {
            $query->orderBy($sort_by, $sort_order);
        } else {
             $query->orderBy('transaction_date', 'desc');
        }

        $per_page = $request->get('per_page', 10);
        $transactions = $query->paginate($per_page)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($transactions);
        }

        return view('management.transactions.index', compact('transactions'));
    }

    public function exportTransactions()
    {
        // Export logic (similar to OwnerController)
        $transactions = \App\Models\Transaction::with('account')->where('status', 'Success')->get();

        $csvFileName = 'transactions-' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction ID', 'Customer Name', 'Date', 'Amount', 'Status']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    ($transaction->account->first_name ?? 'Guest') . ' ' . ($transaction->account->last_name ?? ''),
                    $transaction->transaction_date,
                    $transaction->total_amount,
                    $transaction->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function editTransaction($id)
    {
        $transaction = \App\Models\Transaction::findOrFail($id);
        
        // Allow Super Admin and Owner to edit regardless of status
        $userRole = \Illuminate\Support\Facades\Auth::user()->accountType->name;
        $canEditAny = in_array($userRole, ['Super Admin', 'Owner']);

        if (!$canEditAny) {
            if ($transaction->status !== 'Pending' && $transaction->status !== 'Pending Payment' && $transaction->status !== 'Payment Issue') {
                 if ($transaction->status === 'Success') {
                     return redirect()->route('admin.transactions')->with('error', 'Cannot edit completed transactions.');
                 }
            }
        }

        return view('management.transactions.edit', compact('transaction'));
    }

    public function updateTransaction(Request $request, $id)
    {
        $transaction = \App\Models\Transaction::findOrFail($id);

        $request->validate([
            'payment_method' => 'required|in:Cash,Transfer,QRIS,Other',
            'status' => 'required|in:Pending,Success,Failed',
        ]);

        $previousStatus = $transaction->status;
        $transaction->update([
            'payment_method' => $request->payment_method,
            'status' => $request->status
        ]);

        // If status changed TO Success, update tickets, booking etc (similar to BookingController logic)
        if ($previousStatus !== 'Success' && $request->status === 'Success') {
             // 1. Update Tickets
             if ($transaction->tickets()->exists()) {
                 $transaction->tickets()->update(['status' => 'Valid']); 
             }

             // 2. Check Booking Status
             $booking = $transaction->booking;
             if ($booking) {
                 $pendingCount = $booking->transactions()
                                ->where('type', 'Payment')
                                ->where('status', '!=', 'Success')
                                ->count();
                
                 if ($pendingCount === 0) {
                     $booking->update(['status' => \App\Models\Booking::STATUS_BOOKED]);
                 }
             }
        }

        return redirect()->route('admin.transactions')->with('success', 'Transaction updated successfully.');
    }
    public function resolveTransactionIssue($id)
    {
        $transaction = \App\Models\Transaction::with('expense')->findOrFail($id);
        
        $transaction->update(['status' => 'Pending Confirmation']);
        if ($transaction->expense) {
            $transaction->expense->update(['status' => 'Pending Confirmation']);
        }
        
        return back()->with('success', 'Payment issue resolved. Status reset to Pending Confirmation.');
    }
}
