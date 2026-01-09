@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Owner Dashboard</h3>
                <p>{{ __("You're logged in as an Owner!") }}</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('owner.users') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-purple-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Manage Users</h4>
                        <p class="text-sm text-gray-600">Manage Admin & Customer accounts.</p>
                    </a>
                    <a href="{{ route('owner.reports') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-green-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">Revenue Reports</h4>
                        <p class="text-sm text-gray-600">View daily and monthly earnings.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
