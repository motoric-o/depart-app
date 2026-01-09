@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Admin Dashboard</h3>
                <p>{{ __("You're logged in as an Admin!") }}</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.users') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Users</h4>
                        <p class="text-sm text-gray-600">View and edit user accounts.</p>
                    </a>
                    <a href="{{ route('admin.buses') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Buses</h4>
                        <p class="text-sm text-gray-600">Add or remove buses from fleet.</p>
                    </a>
                    <a href="{{ route('admin.routes') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Routes</h4>
                        <p class="text-sm text-gray-600">Configure travel routes.</p>
                    </a>
                    <a href="{{ route('admin.schedules') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Schedules</h4>
                        <p class="text-sm text-gray-600">Schedule buses and prices.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
