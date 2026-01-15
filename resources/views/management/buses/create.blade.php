@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Add New Bus</h2>
                    <a href="{{ route('admin.buses') }}" class="text-gray-600 hover:text-gray-900">Back to List</a>
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

                <form action="{{ route('admin.buses.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bus_number" class="block text-sm font-medium text-gray-700">Bus Number</label>
                            <input type="text" name="bus_number" id="bus_number" value="{{ old('bus_number') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. B-1234-XYZ">
                        </div>

                        <div>
                            <label for="bus_type" class="block text-sm font-medium text-gray-700">Bus Type</label>
                            <input type="text" name="bus_type" id="bus_type" value="{{ old('bus_type') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Executive, Economy">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" required min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="quota" class="block text-sm font-medium text-gray-700">Quota</label>
                            <input type="number" name="quota" id="quota" value="{{ old('quota') }}" required min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="seat_rows" class="block text-sm font-medium text-gray-700">Seat Rows</label>
                            <input type="number" name="seat_rows" id="seat_rows" value="{{ old('seat_rows') }}" required min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="seat_columns" class="block text-sm font-medium text-gray-700">Seat Columns</label>
                            <input type="number" name="seat_columns" id="seat_columns" value="{{ old('seat_columns') }}" required min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Bus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
