@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700 mb-4 inline-block">&larr; Back to Dashboard</a>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h4 class="text-lg font-bold mb-4">Request Funds</h4>
                        <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="type" value="operational">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <input type="text" name="description" required placeholder="e.g. Fuel, Maintenance" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                <label class="block text-sm font-medium text-gray-700">Proof File (Optional)</label>
                                <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="md:col-span-2" x-data="requestManager()">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h4 class="text-lg font-bold mb-4">My Requests History</h4>
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
                                            @if($expense->proof_file)
                                                <br>
                                                <a href="{{ asset('storage/' . $expense->proof_file) }}" target="_blank" class="text-xs text-blue-600 hover:underline">View File</a>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($expense->status == 'Paid') bg-green-100 text-green-800
                                                @elseif($expense->status == 'Confirmed') bg-green-100 text-green-800
                                                @elseif($expense->status == 'In Process') bg-blue-100 text-blue-800
                                                @elseif(in_array($expense->status, ['Rejected', 'Canceled', 'Failed'])) bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $expense->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                            @if($expense->status == 'Paid')
                                                <button type="button" @click="confirmReceipt('{{ $expense->id }}')" class="bg-green-600 text-white hover:bg-green-700 font-bold rounded px-4 py-2 text-xs">Confirm Receipt</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($expenses->isEmpty())
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No requests found.</td>
                                    </tr>
                                    @endif
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
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `
                    <ul class="text-left">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                confirmButtonColor: '#2563EB'
            });
        @endif

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    });

    function requestManager() {
        return {
            confirmReceipt(id) {
                if(!confirm('Have you received the fund? This will mark the request as Confirmed.')) return;
                
                fetch(`/api/admin/expenses/${id}/confirm`, {
                    method: 'PUT',
                     headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.id) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error occurred');
                    }
                })
                .catch(err => alert('Error connecting to server'));
            }
        }
    }
</script>
@endsection
