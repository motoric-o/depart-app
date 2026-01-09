<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Bus;
use App\Models\Route as BusRoute; // Alias to avoid conflict with Facade

use App\Models\AccountType;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        // View all users, but UI will likely only show actions for customers if we wanted to be strict in view too.
        // For now, list all.
        $users = Account::with('accountType')->paginate(10);
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

    public function buses()
    {
        $buses = Bus::paginate(10);
        return view('admin.buses.index', compact('buses'));
    }

    public function routes()
    {
        $routes = BusRoute::with('destination')->paginate(10);
        return view('admin.routes.index', compact('routes'));
    }
}
