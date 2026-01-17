<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    use \App\Traits\ApiSearchable;

    public function index(Request $request)
    {
        $query = Expense::with(['account.accountType', 'transaction.paymentIssueProofs']);

        // RBAC Filter
        if (Gate::allows('view-financial-reports') || Gate::allows('approve-expense')) {
            // Can see all (Owner, Admin, FinAdmin)
        } else {
            // Can only see own (Driver, Ops Admin maybe?)
            $query->where('account_id', Auth::id());
        }

        // Apply Filters (Handled by ApiSearchable mostly, but custom logic preserved/mapped)
        $searchable = [
            'description' => 'like',
            'type' => 'like',
        ];
        
        // Manual Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $sortable = ['date', 'amount', 'description', 'status', 'created_at', 'type'];

        $expenses = $this->applyApiParams($query, $request, $searchable, $sortable, ['field' => 'created_at', 'order' => 'desc']);

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        Gate::authorize('create-expense');

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:reimbursement,operational,maintenance,salary,other',
            'date' => 'required|date',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        // Auto-approve if user can approve expenses (Wait, typically Admins verify requests, even from other admins. Let's keep it Pending unless specified otherwise)
        // For now, let's stick to Pending for everyone to enable the flow.
        // Or if Ops Admin creates it, it's 'Pending'.
        $status = 'Pending';
        if (Gate::allows('approve-expense') && $request->type !== 'operational') {
            // If it's a simple expense by an admin, maybe auto-approved?
            // But if it's an 'operational' request flow, it typically needs approval.
            // Let's force Pending for 'operational' to ensure flow.
             $status = 'Approved';
        }
        if ($request->type === 'operational') {
            $status = 'Pending';
        }

        $path = null;
        if ($request->hasFile('proof_file')) {
             $path = $request->file('proof_file')->store('expenses', 'public');
        }

        $expense = Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => $status,
            'date' => $request->date,
            'account_id' => Auth::id(),
            'proof_file' => $path
        ]);

        return response()->json($expense, 201);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        if ($expense->account_id !== Auth::id() && !Gate::allows('approve-expense')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expense->update($request->only(['description', 'amount', 'type', 'date']));
        return response()->json($expense);
    }

    public function verify(Request $request, $id)
    {
        Gate::authorize('approve-expense');
        
        $expense = Expense::findOrFail($id);
        // Added 'Confirmed' to valid statuses
        $request->validate(['status' => 'required|in:Approved,In Process,Pending Confirmation,Paid,Payment Issue,Rejected,Processed,Canceled,Failed,Confirmed']);
        
        $expense->update(['status' => $request->status]);
        
        return response()->json($expense);
    }

    public function confirm($id)
    {
        $expense = Expense::findOrFail($id);
        
        // Only the creator can confirm receipt
        if ($expense->account_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the creator can confirm receipt.'], 403);
        }

        if ($expense->status !== 'Paid') {
             return response()->json(['message' => 'Expense must be Paid before confirmation.'], 400);
        }

        $expense->update(['status' => 'Confirmed']);
        
        return response()->json($expense);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        
        if ($expense->account_id !== Auth::id() && !Gate::allows('approve-expense')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $expense->delete();
        return response()->json(null, 204);
    }
}
