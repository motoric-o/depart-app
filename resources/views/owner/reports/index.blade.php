@extends('layouts.app')

@section('content')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-area, #printable-area * {
            visibility: visible;
        }
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
<div class="py-12" id="printable-area">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 no-print">
            <a href="{{ route('owner.dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
        </div>
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Revenue Reports</h2>
            <div class="relative no-print" id="export-menu-container">
                <button id="export-menu-button" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
                    Export <span class="ml-2">&#9662;</span>
                </button>
                <div id="export-menu-dropdown" class="absolute right-0 top-full pt-1 w-48 z-10 hidden">
                    <div class="bg-white rounded-md shadow-lg py-1 border border-gray-100">
                        <a href="{{ route('owner.reports.export') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Download CSV
                        </a>
                        <button onclick="window.print()" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Print / Save as PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('export-menu-container');
                const dropdown = document.getElementById('export-menu-dropdown');
                let timeoutId;

                container.addEventListener('mouseenter', function() {
                    clearTimeout(timeoutId);
                    dropdown.classList.remove('hidden');
                });

                container.addEventListener('mouseleave', function() {
                    timeoutId = setTimeout(() => {
                        dropdown.classList.add('hidden');
                    }, 100);
                });
            });
        </script>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm font-medium uppercase">Total Revenue</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">All time earnings</div>
            </div>

            <!-- Monthly Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm font-medium uppercase">Monthly Revenue</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">
                    Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Earnings for {{ now()->format('F Y') }}</div>
            </div>

            <!-- Daily Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm font-medium uppercase">Today's Revenue</div>
                <div class="mt-2 text-3xl font-bold text-green-600">
                    Rp {{ number_format($dailyRevenue, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">{{ now()->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-bold mb-4">Recent Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentTransactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $transaction->account->first_name ?? 'N/A' }} {{ $transaction->account->last_name ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M d, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $transaction->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No transactions found.</td>
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
