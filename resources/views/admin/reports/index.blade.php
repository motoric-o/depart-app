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
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
        </div>
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Financial Overview</h2>
            <div class="relative no-print" id="export-menu-container">
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
                    Print Report
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm font-medium uppercase">Total Revenue</div>
                <div class="mt-2 text-3xl font-bold text-green-600">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Confirmed transactions</div>
            </div>

            <!-- Total Expenses Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm font-medium uppercase">Total Expenses</div>
                <div class="mt-2 text-3xl font-bold text-red-600">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Approved/In Process</div>
            </div>

            <!-- Net Profit Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm font-medium uppercase">Net Profit</div>
                <div class="mt-2 text-3xl font-bold {{ $netProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Revenue - Expenses</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Performing Routes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Top Performing Routes</h3>
                    <div class="overflow-x-auto">
                        @if($topRoutes->isEmpty())
                            <p class="text-gray-500 text-sm">No route data available.</p>
                        @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topRoutes as $route)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="font-medium">{{ $route->route_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $route->source_name ?? $route->source }} &rarr; {{ $route->destination_name ?? $route->destination }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $route->total_bookings }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">Rp {{ number_format($route->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Recent Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">#{{ $transaction->id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M d') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-sm text-gray-500 text-center">No transactions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
