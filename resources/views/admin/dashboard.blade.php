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
                    <a href="{{ route('admin.users') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">{{ Auth::user()->can('manage-users') ? 'Manage Users' : 'View Users' }}</h4>
                        <p class="text-sm text-gray-600">{{ Auth::user()->can('manage-users') ? 'View and edit user accounts.' : 'View user accounts.' }}</p>
                    </a>
                    @endcan
                    
                    @can('view-buses')
                    <a href="{{ route('admin.buses') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">{{ Auth::user()->can('manage-buses') ? 'Manage Buses' : 'View Buses' }}</h4>
                        <p class="text-sm text-gray-600">{{ Auth::user()->can('manage-buses') ? 'Add or remove buses from fleet.' : 'View fleet status.' }}</p>
                    </a>
                    @endcan
                    
                    @can('view-routes')
                    <a href="{{ route('admin.routes') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">{{ Auth::user()->can('manage-routes') ? 'Manage Routes' : 'View Routes' }}</h4>
                        <p class="text-sm text-gray-600">{{ Auth::user()->can('manage-routes') ? 'Configure travel routes.' : 'View available routes.' }}</p>
                    </a>
                    @endcan
                    
                    @can('view-schedules')
                    <a href="{{ route('admin.schedules') }}" class="block p-4 border rounded shadow-sm bg-blue-50 hover:bg-blue-100 transition duration-150">
                        <h4 class="font-semibold text-blue-700">{{ Auth::user()->can('manage-schedules') ? 'Manage Schedules' : 'View Schedules' }}</h4>
                        <p class="text-sm text-gray-600">{{ Auth::user()->can('manage-schedules') ? 'Schedule buses and prices.' : 'View bus schedules.' }}</p>
                    </a>
                    @endcan

                    <!-- Financial & Operations -->
                    @can('view-financial-reports')
                    <a href="{{ route('owner.reports') }}" class="block p-4 border rounded shadow-sm bg-green-50 hover:bg-green-100 transition duration-150">
                        <h4 class="font-semibold text-green-700">Financial Reports</h4>
                        <p class="text-sm text-gray-600">View revenue and expenses.</p>
                    </a>
                    @endcan

                    @can('approve-expense')
                    <a href="{{ route('admin.expenses') }}" class="block p-4 border rounded shadow-sm bg-green-50 hover:bg-green-100 transition duration-150">
                        <h4 class="font-semibold text-green-700">Manage Expenses</h4>
                        <p class="text-sm text-gray-600">Manage expense requests.</p>
                    </a>
                    @endcan
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
