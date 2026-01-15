@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Manage Expenses</h3>
                </div>

                <!-- Toolbar -->
                <div class="w-full" x-data="{ showFilters: {{ request()->anyFilled(['type', 'status', 'date_from', 'date_to']) ? 'true' : 'false' }} }">
                    <form action="{{ route('admin.expenses') }}" method="GET">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search expenses..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                            
                            <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Sort & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Search</button>
                            
                            @if(request()->anyFilled(['search', 'type', 'status', 'date_from']))
                            <a href="{{ route('admin.expenses') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors">
                                Clear
                            </a>
                            @endif

                            @can('create-expense')
                            <a href="{{ route('admin.expenses.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors ml-auto">
                                Record New Expense
                            </a>
                            @endcan
                        </div>
                        
                        <div x-show="showFilters" x-collapse style="display: none;" class="w-full grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">All Types</option>
                                    <option value="reimbursement" {{ request('type') == 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                                    <option value="operational" {{ request('type') == 'operational' ? 'selected' : '' }}>Operational</option>
                                    <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="salary" {{ request('type') == 'salary' ? 'selected' : '' }}>Salary</option>
                                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">All Status</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="Processed" {{ request('status') == 'Processed' ? 'selected' : '' }}>Processed</option>
                                    <option value="Canceled" {{ request('status') == 'Canceled' ? 'selected' : '' }}>Canceled</option>
                                    <option value="Failed" {{ request('status') == 'Failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                            </div>
                            <div class="flex items-end">
                            </div>
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
                                        {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
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
                                            @if($expense->status == 'Approved') bg-green-100 text-green-800
                                            @elseif($expense->status == 'Processed') bg-blue-100 text-blue-800
                                            @elseif(in_array($expense->status, ['Rejected', 'Canceled', 'Failed'])) bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $expense->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @can('approve-expense')
                                            @if($expense->status == 'Pending')
                                                <form action="{{ route('admin.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Approved">
                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-2 font-bold">Approve</button>
                                                </form>
                                                <form action="{{ route('admin.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Rejected">
                                                    <button type="submit" class="text-red-600 hover:text-red-900 mr-2 font-bold">Reject</button>
                                                </form>
                                            @elseif($expense->status == 'Approved')
                                                 <form action="{{ route('admin.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Processed">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 mr-2 font-bold">Process</button>
                                                </form>
                                            @endif
                                            
                                            @if(in_array($expense->status, ['Pending', 'Approved']))
                                                <form action="{{ route('admin.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Canceled">
                                                    <button type="submit" class="text-gray-600 hover:text-gray-900 mr-2 font-bold">Cancel</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No expenses found.</td>
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
