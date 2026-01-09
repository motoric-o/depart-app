@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Manage Schedules</h2>
                    <a href="{{ route('admin.schedules.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Schedule</a>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrival</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($schedules as $schedule)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $schedule->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $schedule->route->source }} -> {{ $schedule->route->destination->city_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $schedule->bus->bus_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('M d, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('M d, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $schedule->status === 'Scheduled' ? 'bg-green-100 text-green-800' : 
                                           ($schedule->status === 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $schedule->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                    <form action="{{ route('admin.schedules.delete', $schedule->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
