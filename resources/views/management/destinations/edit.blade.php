@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Edit Destination</h2>
                    <a href="{{ route('admin.destinations') }}" class="text-gray-600 hover:text-gray-900">Back to List</a>
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

                <form action="{{ route('admin.destinations.update', $destination->code) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $destination->code) }}" required maxlength="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 uppercase" placeholder="e.g. JKT">
                            <p class="mt-1 text-xs text-gray-500">Max 5 characters. Must be unique.</p>
                        </div>

                        <div>
                            <label for="city_name" class="block text-sm font-medium text-gray-700">City Name</label>
                            <input type="text" name="city_name" id="city_name" value="{{ old('city_name', $destination->city_name) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" placeholder="e.g. Jakarta">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Destination
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
