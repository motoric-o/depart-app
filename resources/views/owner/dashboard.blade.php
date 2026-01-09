@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Owner Dashboard</h3>
                <p>{{ __("You're logged in as an Owner!") }}</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 border rounded shadow-sm bg-green-50">
                        <h4 class="font-semibold text-green-700">Revenue Reports</h4>
                        <p class="text-sm text-gray-600">View daily and monthly earnings.</p>
                    </div>
                    <div class="p-4 border rounded shadow-sm bg-green-50">
                        <h4 class="font-semibold text-green-700">Fleet Status</h4>
                        <p class="text-sm text-gray-600">Check bus maintenance status.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
