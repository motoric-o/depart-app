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
            // If Ops Admin manages others, maybe they see all? 
            // Let's assume Ops Admin creates expenses mostly, but Financial Admin approves.
            // Safe bet: Only Approvers see all. Others see own.
            $query->where('account_id', Auth::id());
        }

        // Apply Filters (Handled by ApiSearchable mostly, but custom logic preserved/mapped)
        $searchable = [
            'description' => 'like',
            'type' => 'like',
            // 'status' => 'exact' // If we want to support status filter via 'search', but strict filter is better:
        ];
        
        // Manual Status filter (ApiSearchable can handle this if we map it, but let's keep explicit for now or merge)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) { // ApiSearchable might not capture exact match well if 'like' is used, duplicate safe.
            $query->where('type', $request->type);
        }

        $sortable = ['date', 'amount', 'description', 'status', 'created_at'];

        $expenses = $this->applyApiParams($query, $request, $searchable, $sortable, ['field' => 'date', 'order' => 'desc']);

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
        ]);

        // Auto-approve if user can approve expenses
        $status = Gate::allows('approve-expense') ? 'Approved' : 'Pending';

        $expense = Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => $status,
            'date' => $request->date,
            'account_id' => Auth::id(),
        ]);

        return response()->json($expense, 201);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        // Only owner/creator can edit? Or Approver?
        // Usually, if Pending, Creator can edit. If Approved, Locked?
        // Let's allow Approvers to edit details too.
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
        $request->validate(['status' => 'required|in:Approved,In Process,Pending Confirmation,Paid,Payment Issue,Rejected,Processed,Canceled,Failed']);
        
        $expense->update(['status' => $request->status]);
        
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
