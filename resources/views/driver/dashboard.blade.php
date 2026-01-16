@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h3 class="text-2xl font-bold mb-6 text-gray-800">Driver Dashboard</h3>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                <div class="font-semibold text-lg">Upcoming Trips</div>
                <div class="text-3xl font-bold mt-2">{{ $upcomingSchedules->count() }}</div>
                <div class="text-sm opacity-80 mt-1">Scheduled for you</div>
            </div>
            <div class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                <div class="font-semibold text-lg">Today's Jobs</div>
                <div class="text-3xl font-bold mt-2">{{ $todaysTrips->count() }}</div>
                <div class="text-sm opacity-80 mt-1">Departure today</div>
            </div>
            <a href="{{ route('driver.expenses') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                 <div class="font-semibold text-lg">Expenses</div>
                 <div class="text-3xl font-bold mt-2">Manage</div>
                 <div class="text-sm opacity-80 mt-1">Submit & View</div>
            </a>
            <a href="{{ route('driver.earnings') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                 <div class="font-semibold text-lg">Earnings</div>
                 <div class="text-3xl font-bold mt-2">History</div>
                 <div class="text-sm opacity-80 mt-1">View Income</div>
            </a>
        </div>

        <!-- Today's Schedule -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <h4 class="text-lg font-bold mb-4">Today's Departures</h4>
                @if($todaysTrips->isEmpty())
                    <p class="text-gray-500">No active trips for today.</p>
                @else
                    <div class="grid gap-4">
                        @foreach($todaysTrips as $schedule)
                        <div class="border rounded-lg p-4 flex justify-between items-center hover:bg-gray-50 transition">
                            <div>
                                <div class="font-bold text-lg">{{ optional($schedule->route->sourceDestination)->city_name ?? $schedule->route->source }} ({{ $schedule->route->source }}) <span class="text-gray-400">→</span> {{ optional($schedule->route->destination)->city_name ?? $schedule->route->destination_code }} ({{ $schedule->route->destination_code }})</div>
                                <div class="text-gray-600">Bus: {{ $schedule->bus->bus_number }} - {{ $schedule->bus->bus_type }}</div>
                                <div class="text-sm text-gray-500 mt-1">Departs: {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</div>
                            </div>
                            <a href="{{ route('driver.schedules.show', $schedule->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">View & Check-in</a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Schedule List -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h4 class="text-lg font-bold mb-4">Upcoming Schedule</h4>
                @if($upcomingSchedules->isEmpty())
                    <p class="text-gray-500">No upcoming schedules found.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bus</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($upcomingSchedules as $schedule)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($schedule->route->sourceDestination)->city_name ?? $schedule->route->source }} ({{ $schedule->route->source }}) → {{ optional($schedule->route->destination)->city_name ?? $schedule->route->destination_code }} ({{ $schedule->route->destination_code }})
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">
                                    {{ $schedule->bus->bus_number }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm">
                                    <a href="{{ route('driver.schedules.show', $schedule->id) }}" class="text-blue-600 hover:text-blue-900">Details</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
