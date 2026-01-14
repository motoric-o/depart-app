@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-4">
                    <a href="{{ route('owner.dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Manage Expenses</h3>
                    <a href="{{ route('owner.expenses.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Recorded New Expense</a>
                </div>

                <!-- Filters -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <form id="filterForm" action="{{ route('owner.expenses') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Description..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" onchange="this.form.submit()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Types</option>
                                <option value="reimbursement" {{ request('type') == 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                                <option value="operational" {{ request('type') == 'operational' ? 'selected' : '' }}>Operational</option>
                                <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="salary" {{ request('type') == 'salary' ? 'selected' : '' }}>Salary</option>
                                <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" onchange="this.form.submit()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Status</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($expenses as $expense)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $expense->date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $expense->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $expense->type == 'reimbursement' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $expense->type == 'operational' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $expense->type == 'maintenance' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $expense->type == 'salary' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $expense->type == 'other' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($expense->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $expense->status == 'Approved' ? 'bg-green-100 text-green-800' : 
                                              ($expense->status == 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $expense->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($expense->status == 'Pending')
                                            <form action="{{ route('owner.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="status" value="Approved">
                                                <button type="submit" class="text-green-600 hover:text-green-900 mr-2 font-bold">Approve</button>
                                            </form>
                                            <form action="{{ route('owner.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="status" value="Rejected">
                                                <button type="submit" class="text-red-600 hover:text-red-900 mr-2 font-bold">Reject</button>
                                            </form>
                                        @else
                                            <a href="{{ route('owner.expenses.show', $expense->id) }}" class="text-gray-600 hover:text-gray-900 font-bold">View Detail</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No expenses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
