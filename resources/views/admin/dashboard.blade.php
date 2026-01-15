@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Admin Dashboard</h3>
                <p>{{ __("You're logged in as an Admin!") }}</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Management Links (Visible to all Admins, Actions protected on page) --}}
                    @can('view-users')
                    <a href="{{ route('admin.users') }}" class="block bg-indigo-600 rounded-lg shadow p-6 text-white hover:bg-indigo-700 transition">
                        <div class="text-3xl font-bold">Users</div>
                        <div class="text-sm opacity-80 mt-2">{{ Auth::user()->can('manage-users') ? 'View and edit user accounts.' : 'View user accounts.' }}</div>
                    </a>
                    @endcan
                    
                    @can('view-buses')
                    <a href="{{ route('admin.buses') }}" class="block bg-indigo-600 rounded-lg shadow p-6 text-white hover:bg-indigo-700 transition">
                        <div class="text-3xl font-bold">Buses</div>
                        <div class="text-sm opacity-80 mt-2">{{ Auth::user()->can('manage-buses') ? 'Add or remove buses from fleet.' : 'View fleet status.' }}</div>
                    </a>
                    @endcan
                    
                    @can('view-routes')
                    <a href="{{ route('admin.routes') }}" class="block bg-indigo-600 rounded-lg shadow p-6 text-white hover:bg-indigo-700 transition">
                        <div class="text-3xl font-bold">Routes</div>
                        <div class="text-sm opacity-80 mt-2">{{ Auth::user()->can('manage-routes') ? 'Configure travel routes.' : 'View available routes.' }}</div>
                    </a>
                    @endcan
                    
                    @can('view-schedules')
                    <a href="{{ route('admin.schedules') }}" class="block bg-indigo-600 rounded-lg shadow p-6 text-white hover:bg-indigo-700 transition">
                        <div class="text-3xl font-bold">Schedules</div>
                        <div class="text-sm opacity-80 mt-2">{{ Auth::user()->can('manage-schedules') ? 'Schedule buses and prices.' : 'View bus schedules.' }}</div>
                    </a>
                    @endcan

                    <!-- Financial & Operations -->
                    @can('view-financial-reports')
                    <a href="{{ route('owner.reports') }}" class="block bg-indigo-600 rounded-lg shadow p-6 text-white hover:bg-indigo-700 transition">
                        <div class="text-3xl font-bold">Finance</div>
                        <div class="text-sm opacity-80 mt-2">View revenue and expenses.</div>
                    </a>
                    @endcan

                    @can('approve-expense')
                    <a href="{{ route('admin.expenses') }}" class="block bg-indigo-600 rounded-lg shadow p-6 text-white hover:bg-indigo-700 transition">
                        <div class="text-3xl font-bold">Expenses</div>
                        <div class="text-sm opacity-80 mt-2">Manage expense requests.</div>
                    </a>
                    @endcan
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
