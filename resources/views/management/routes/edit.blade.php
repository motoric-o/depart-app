@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Edit Route: {{ $route->id }}</h2>
                    <a href="{{ route('admin.routes') }}" class="text-gray-600 hover:text-gray-900">Back to List</a>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.routes.update', $route->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700">Source City</label>
                            <input type="text" name="source" id="source" value="{{ old('source', $route->source) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" placeholder="e.g. Jakarta">
                        </div>

                        <div>
                            <label for="destination_code" class="block text-sm font-medium text-gray-700">Destination</label>
                            <select name="destination_code" id="destination_code" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                                <option value="">Select Destination</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->code }}" {{ old('destination_code', $route->destination_code) == $destination->code ? 'selected' : '' }}>
                                        {{ $destination->city_name }} ({{ $destination->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="distance" class="block text-sm font-medium text-gray-700">Distance (km)</label>
                            <input type="number" name="distance" id="distance" value="{{ old('distance', $route->distance) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        </div>

                        <div>
                            <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Estimated Duration (minutes)</label>
                            <input type="number" name="estimated_duration" id="estimated_duration" value="{{ old('estimated_duration', $route->estimated_duration) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Route
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
