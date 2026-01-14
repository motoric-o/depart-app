@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('driver.dashboard') }}" class="text-gray-500 hover:text-gray-700 mb-4 inline-block">&larr; Back to Dashboard</a>
        <h3 class="text-2xl font-bold mb-6 text-gray-800">My Earnings</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                 <h4 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Approved Income (Reimb.)</h4>
                 <div class="text-3xl font-bold text-green-600 mt-2">Rp {{ number_format($earnings, 0, ',', '.') }}</div>
                 <p class="text-xs text-gray-400 mt-2">Currently showing total approved reimbursements as income reference.</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h4 class="text-lg font-bold mb-4">Earnings History</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($earningHistory as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $item->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                        + Rp {{ number_format($item->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No earnings recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
