<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Expense; // Import Expense model
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function dashboard()
    {
        // Use Stored Procedure for Dashboard Stats to reduce aggregation load
        $statsResult = \Illuminate\Support\Facades\DB::select("SELECT * FROM sp_get_owner_dashboard_stats()");
        $dashboardStats = $statsResult[0];

        // Use the new View Model to get optimized stats
        $routeStats = \App\Models\RouteStat::orderByDesc('total_bookings')
            ->take(5)
            ->get();

        $totalExpenses = \App\Models\Expense::sum('amount');
        // Calculate Net Profit
        $netProfit = ($dashboardStats->total_revenue ?? 0) - $totalExpenses;
        
        $recentExpenses = \App\Models\Expense::orderByDesc('date')->take(5)->get();

        return view('owner.dashboard', compact('routeStats', 'dashboardStats', 'totalExpenses', 'recentExpenses', 'netProfit'));
    }

    // User Management is led by AdminController (Unified)


    public function reports()
    {
        $totalRevenue = \App\Models\Transaction::where('status', 'Success')->sum('total_amount');
        $dailyRevenue = \App\Models\Transaction::where('status', 'Success')->whereDate('transaction_date', today())->sum('total_amount');
        $monthlyRevenue = \App\Models\Transaction::where('status', 'Success')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('total_amount');
        
        // Get Daily Breakdown for current month via SP
        $dailyBreakdown = \Illuminate\Support\Facades\DB::select("SELECT * FROM sp_get_monthly_revenue(?, ?)", [now()->year, now()->month]); // This variable might be unused in new view but keeping for compatibility if we add charts later
        
        // New Additions for Unified View
        $totalExpenses = \App\Models\Expense::whereNotIn('status', ['Rejected', 'Canceled'])->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        
        $topRoutes = \App\Models\RouteStat::orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $recentTransactions = \App\Models\Transaction::with(['account', 'booking'])
            ->where('status', 'Success')
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        return view('management.reports.index', compact('totalRevenue', 'dailyRevenue', 'monthlyRevenue', 'recentTransactions', 'dailyBreakdown', 'totalExpenses', 'netProfit', 'topRoutes'));
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
        return response()->stream($callback, 200, $headers);
    }

    // Expense Management

    public function expenses(Request $request)
    {
        $query = Expense::query();

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

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();

        return view('owner.expenses.index', compact('expenses'));
    }

    public function createExpense()
    {
        return view('owner.expenses.create');
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:reimbursement,operational,maintenance,salary,other',
            'date' => 'required|date',
        ]);

        Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => 'Approved', // Owner created expenses are auto-approved
            'date' => $request->date,
            'account_id' => Auth::id(),
        ]);

        return redirect()->route('owner.expenses')->with('success', 'Expense created successfully.');
    }

    public function editExpense($id)
    {
        $expense = Expense::findOrFail($id);
        return view('owner.expenses.edit', compact('expense'));
    }

    public function updateExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:reimbursement,operational,maintenance,salary,other',
            'date' => 'required|date',
        ]);

        $expense->update([
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'date' => $request->date,
        ]);

        return redirect()->route('owner.expenses')->with('success', 'Expense updated successfully.');
    }

    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('owner.expenses')->with('success', 'Expense deleted successfully.');
    }
    public function verifyExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Approved,Rejected'
        ]);

        $expense->update(['status' => $request->status]);

        return back()->with('success', 'Expense verified successfully.');
    }

    public function showExpense($id)
    {
        $expense = Expense::with('account')->findOrFail($id);
        return view('owner.expenses.show', compact('expense'));
    }
}
