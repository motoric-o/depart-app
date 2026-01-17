@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('driver.dashboard') }}" class="text-gray-500 hover:text-gray-700 mb-4 inline-block">&larr; Back to Dashboard</a>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h4 class="text-lg font-bold mb-4">Request Reimbursement</h4>
                        <form action="{{ route('driver.expenses.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <input type="text" name="description" required placeholder="e.g. Fuel, Toll, Parking" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Amount (Rp)</label>
                                <input type="number" name="amount" required min="0" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Proof (Scan/PDF)</label>
                                <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="md:col-span-2" x-data="{ showIssueModal: false, selectedExpenseId: null }">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h4 class="text-lg font-bold mb-4">My Reimbursement History</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($expenses as $expense)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $expense->date->format('d M Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ $expense->description }}
                                            @if($expense->transaction && $expense->transaction->paymentIssueProofs->isNotEmpty())
                                                @php 
                                                    $issueProof = $expense->transaction->paymentIssueProofs->last();
                                                @endphp
                                                <br>
                                                <a href="{{ asset('storage/' . $issueProof->file_path) }}" target="_blank" class="text-xs text-red-600 hover:underline font-bold">View Issue Proof</a>
                                            @elseif($expense->proof_file)
                                                <br>
                                                <a href="{{ asset('storage/' . $expense->proof_file) }}" target="_blank" class="text-xs text-blue-600 hover:underline">View Receipt</a>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($expense->status == 'Paid') bg-green-100 text-green-800
                                                @elseif($expense->status == 'In Process') bg-blue-100 text-blue-800
                                                @elseif($expense->status == 'Pending Confirmation') bg-orange-100 text-orange-800
                                                @elseif(in_array($expense->status, ['Rejected', 'Canceled', 'Failed', 'Payment Issue'])) bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $expense->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                            @if($expense->status == 'Pending Confirmation')
                                                <form action="{{ route('driver.expenses.confirm', $expense->id) }}" method="POST" class="inline-block mr-2">
                                                    @csrf
                                                    <button type="submit" class="bg-green-600 text-white hover:bg-green-700 font-bold rounded px-4 py-2 text-xs">Finish</button>
                                                </form>
                                                <button @click="showIssueModal = true; selectedExpenseId = '{{ $expense->id }}'" class="bg-red-600 text-white hover:bg-red-700 font-bold rounded px-4 py-2 text-xs">Funds not received?</button>
                                            @elseif($expense->status == 'Paid')
                                                <button @click="showIssueModal = true; selectedExpenseId = '{{ $expense->id }}'" class="bg-red-600 text-white hover:bg-red-700 font-bold rounded px-4 py-2 text-xs">Funds not received?</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $expenses->links() }}
                        </div>
                    </div>
                </div>
                
                <!-- Report Issue Modal -->
                <div x-show="showIssueModal" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    
                    <!-- Background backdrop -->
                    <div x-show="showIssueModal" 
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-[rgba(17,24,39,0.2)] backdrop-blur-sm transition-opacity"></div>

                    <div class="fixed inset-0 z-10 overflow-y-auto">
                        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            
                            <!-- Modal panel -->
                            <div x-show="showIssueModal" 
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 @click.outside="showIssueModal = false"
                                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                
                                <form :action="'/driver/expenses/' + selectedExpenseId + '/issue'" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Report Payment Issue</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500 mb-4">
                                                        Please describe the issue and upload any proof (e.g., bank statement screenshot).
                                                    </p>
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700">Message / Description</label>
                                                        <textarea name="message" rows="3" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700">Proof of Issue (Image/PDF)</label>
                                                        <input type="file" name="proof_file" required accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Report Issue
                                        </button>
                                        <button type="button" @click="showIssueModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
