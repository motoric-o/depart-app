<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use App\Traits\ApiSearchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiSearchable;

    public function index(Request $request)
    {
        // \Illuminate\Support\Facades\Gate::authorize('manage-users'); // Allow all admins to view
        $query = Account::with('accountType');

        $searchable = [
            'id' => 'like',
            'first_name' => 'like',
            'last_name' => 'like',
            'email' => 'like',
            'accountType.name' => 'like'
        ];

        $sortable = ['id', 'first_name', 'last_name', 'email', 'phone', 'account_type_id'];

        // Filter based on role permissions
        // Admin sees Customers, Drivers. Owner sees Admin too.
        // Legacy AdminController Showed All? Or just managed subsets?
        // Legacy AdminController index calls `Account::with('accountType')->paginate(10)`. It showed ALL.
        // But store/update/delete had restrictions.
        // I will stick to showing ALL, or maybe filter?
        // Let's replicate strict logic: Show all, but restrictions on actions.
        
        $users = $this->applyApiParams($query, $request, $searchable, $sortable, ['field' => 'id', 'order' => 'asc']);

        return response()->json($users);
    }

    public function show($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
        $user = Account::with('accountType')->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
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
        if ($request->user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }
        
        if (!in_array($role->name, $allowedRoles)) {
            throw ValidationException::withMessages(['account_type_id' => 'You do not have permission to create a user with this role.']);
        }

        DB::statement("CALL sp_create_customer(?, ?, ?, ?, ?, ?, ?)", [
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->birthdate,
            Hash::make($request->password),
            $role->id
        ]);

        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
        $user = Account::with('accountType')->findOrFail($id);

        // Permission Check for target User
        $allowedRoles = ['Customer', 'Driver'];
        if ($request->user()->accountType->name === 'Owner') {
             $allowedRoles[] = 'Super Admin';
             $allowedRoles[] = 'Financial Admin';
             $allowedRoles[] = 'Scheduling Admin';
             $allowedRoles[] = 'Operations Admin';
        }

        if (!in_array($user->accountType->name, $allowedRoles)) {
             return response()->json(['message' => 'You do not have permission to edit this account.'], 403);
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
        if (!in_array($role->name, $allowedRoles)) {
            throw ValidationException::withMessages(['account_type_id' => 'Invalid role selected.']);
        }

        DB::statement("CALL sp_update_customer(?, ?, ?, ?, ?, ?, ?)", [
            $user->id,
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->birthdate,
            $role->id
        ]);

        if ($request->filled('password')) {
             $request->validate(['password' => 'string|min:8|confirmed']);
             $user->update(['password_hash' => Hash::make($request->password)]);
        }

        return response()->json($user->fresh('accountType'));
    }

    public function destroy(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
        $user = Account::with('accountType')->findOrFail($id);

        $allowedRoles = ['Customer', 'Driver'];
        if ($request->user()->accountType->name === 'Owner') {
            $allowedRoles[] = 'Super Admin';
            $allowedRoles[] = 'Financial Admin';
            $allowedRoles[] = 'Scheduling Admin';
            $allowedRoles[] = 'Operations Admin';
        }

        if (!in_array($user->accountType->name, $allowedRoles)) {
             return response()->json(['message' => 'You do not have permission to delete this account.'], 403);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}
