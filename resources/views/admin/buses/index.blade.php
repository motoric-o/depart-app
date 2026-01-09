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
                    <h2 class="text-2xl font-bold">Manage Buses</h2>
                    <a href="{{ route('admin.buses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Bus</a>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layout</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($buses as $bus)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bus->bus_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bus->bus_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bus->capacity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bus->seat_rows }} x {{ $bus->seat_columns }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.buses.edit', $bus->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                    <form action="{{ route('admin.buses.delete', $bus->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this bus?');">
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
                    {{ $buses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
