<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Support\Facades\Hash;

class OwnerController extends Controller
{
    public function dashboard()
    {
        return view('owner.dashboard');
    }

    public function users(Request $request)
    {
        $targetRoles = ['Admin', 'Customer'];
        $query = Account::whereHas('accountType', function($q) use ($targetRoles) {
            $q->whereIn('name', $targetRoles);
        })->with('accountType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            if (in_array($request->role, $targetRoles)) {
                $query->whereHas('accountType', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }
        }

        $users = $query->paginate(10)->withQueryString();

        return view('owner.users.index', compact('users'));
    }

    public function createUser()
    {
        // Only Admin and Customer roles available for creation
        $roles = AccountType::whereIn('name', ['Admin', 'Customer'])->get();
        return view('owner.users.create', compact('roles'));
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

        // Verify the selected role is allowed (Admin or Customer)
        $role = AccountType::findOrFail($request->account_type_id);
        if (!in_array($role->name, ['Admin', 'Customer'])) {
            return back()->withErrors(['account_type_id' => 'Invalid role selected.']);
        }

        Account::create([
            'account_type_id' => $role->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'password_hash' => Hash::make($request->password),
        ]);

        return redirect()->route('owner.users')->with('success', 'User created successfully.');
    }

    public function editUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if (!in_array($user->accountType->name, ['Admin', 'Customer'])) {
            return redirect()->route('owner.users')->with('error', 'You can only edit Admin or Customer accounts.');
        }

        $roles = AccountType::whereIn('name', ['Admin', 'Customer'])->get();
        return view('owner.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if (!in_array($user->accountType->name, ['Admin', 'Customer'])) {
            return redirect()->route('owner.users')->with('error', 'You can only edit Admin or Customer accounts.');
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
        if (!in_array($role->name, ['Admin', 'Customer'])) {
            return back()->withErrors(['account_type_id' => 'Invalid role selected.']);
        }

        $user->update([
            'account_type_id' => $role->id,
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

        return redirect()->route('owner.users')->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = Account::with('accountType')->findOrFail($id);

        if (!in_array($user->accountType->name, ['Admin', 'Customer'])) {
            return redirect()->route('owner.users')->with('error', 'You can only delete Admin or Customer accounts.');
        }

        $user->delete();

        return redirect()->route('owner.users')->with('success', 'User deleted successfully.');
    }

    public function reports()
    {
        $totalRevenue = \App\Models\Transaction::where('status', 'Success')->sum('total_amount');
        $dailyRevenue = \App\Models\Transaction::where('status', 'Success')->whereDate('transaction_date', today())->sum('total_amount');
        $monthlyRevenue = \App\Models\Transaction::where('status', 'Success')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('total_amount');
        
        $recentTransactions = \App\Models\Transaction::with(['account', 'booking'])
            ->where('status', 'Success')
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        return view('owner.reports.index', compact('totalRevenue', 'dailyRevenue', 'monthlyRevenue', 'recentTransactions'));
    }

    public function exportCsv()
    {
        $transactions = \App\Models\Transaction::with('account')->where('status', 'Success')->get();

        $csvFileName = 'revenue-reports-' . date('Y-m-d') . '.csv';
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
                    $transaction->account->first_name . ' ' . $transaction->account->last_name,
                    $transaction->transaction_date,
                    $transaction->total_amount,
                    $transaction->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
