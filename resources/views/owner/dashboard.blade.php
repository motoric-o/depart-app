@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Owner Dashboard</h3>
                <p>{{ __("You're logged in as an Owner!") }}</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Fleet Management (Unified Admin Access) -->
                    <a href="{{ route('admin.schedules') }}" class="block p-4 border rounded shadow-sm bg-purple-50 hover:bg-purple-100 transition duration-150">
                        <h4 class="font-semibold text-purple-700">Manage Schedules</h4>
                        <p class="text-sm text-gray-600">Unified Schedule Management.</p>
                    </a>
                    <a href="{{ route('admin.buses') }}" class="block p-4 border rounded shadow-sm bg-purple-50 hover:bg-purple-100 transition duration-150">
                        <h4 class="font-semibold text-purple-700">Manage Buses</h4>
                        <p class="text-sm text-gray-600">Unified Bus Management.</p>
                    </a>
                    <a href="{{ route('admin.routes') }}" class="block p-4 border rounded shadow-sm bg-purple-50 hover:bg-purple-100 transition duration-150">
                        <h4 class="font-semibold text-purple-700">Manage Routes</h4>
                        <p class="text-sm text-gray-600">Unified Route Management.</p>
                    </a>

                    <!-- Owner Specific -->
                    <a href="{{ route('admin.users') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Users</h4>
                        <p class="text-sm text-gray-600">Unified User Management.</p>
                    </a>
                    <a href="{{ route('owner.reports') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Revenue Reports</h4>
                        <p class="text-sm text-gray-600">View daily and monthly earnings.</p>
                    </a>
                    <a href="{{ route('owner.expenses') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Expenses</h4>
                        <p class="text-sm text-gray-600">Track reimbursements and costs.</p>
                    </a>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-bold mb-3">Financial Overview</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white border rounded-lg p-4 shadow-sm">
                            <h5 class="text-gray-500 font-medium">Total Revenue</h5>
                            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($dashboardStats->total_revenue ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white border rounded-lg p-4 shadow-sm">
                            <h5 class="text-gray-500 font-medium">Total Expenses</h5>
                            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h4 class="text-lg font-bold mb-3">Top Performing Routes</h4>
                    <div class="bg-gray-50 border rounded-lg p-4">
                        @if($routeStats->isEmpty())
                            <p class="text-gray-500">No data available yet.</p>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($routeStats as $stat)
                                        <tr>
                                            <td class="px-3 py-2 text-sm font-semibold text-gray-900">{{ $stat->route_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $stat->source_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $stat->destination_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $stat->total_bookings }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">Rp {{ number_format($stat->total_revenue, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">Rp {{ number_format($stat->average_ticket_price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
