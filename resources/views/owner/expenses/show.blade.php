@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('owner.expenses') }}" class="text-gray-500 hover:text-gray-700 mb-4 inline-block">&larr; Back to Expenses</a>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Expense Detail</h3>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $expense->status == 'Approved' ? 'bg-green-100 text-green-800' : 
                          ($expense->status == 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ $expense->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $expense->description }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Amount</label>
                        <p class="mt-1 text-lg font-bold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Date</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $expense->date->format('d M Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1 text-lg text-gray-900 capitalize">{{ $expense->type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Recorded By</label>
                        <p class="mt-1 text-lg text-gray-900">
                            {{ $expense->account->first_name }} {{ $expense->account->last_name }}
                            <span class="text-xs text-gray-500 ml-2">({{ $expense->account->email }})</span>
                        </p>
                    </div>
                </div>

                @if($expense->status == 'Pending' && $expense->type == 'reimbursement')
                    <div class="mt-8 border-t pt-6">
                        <h4 class="text-lg font-bold mb-4">Actions</h4>
                        <form action="{{ route('owner.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                            @csrf
                            <input type="hidden" name="status" value="Approved">
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 mr-4">Approve Reimbursement</button>
                        </form>
                        <form action="{{ route('owner.expenses.verify', $expense->id) }}" method="POST" class="inline-block">
                            @csrf
                            <input type="hidden" name="status" value="Rejected">
                            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700">Reject Request</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
