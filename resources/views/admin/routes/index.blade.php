@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" x-data="{ showFilters: {{ request('sort_by') || request('sort_order') ? 'true' : 'false' }} }">
                <div class="mb-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>
                <div class="mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
                    <h2 class="text-2xl font-bold">Manage Routes</h2>
                    <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 w-full md:w-auto">
                        <form method="GET" action="{{ route('admin.routes') }}" id="filterForm" class="w-full">
                            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search routes..." class="flex-grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                                <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap">
                                    <span>Sort & Filter</span>
                                    <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px]">Search</button>
                                @if(request('search') || request('sort_by'))
                                    <a href="{{ route('admin.routes') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-center flex items-center justify-center border border-transparent h-[42px]">Clear</a>
                                @endif
                            </div>


                        </form>
                        <form action="{{ route('admin.routes.create') }}" method="GET">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-center border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap">Add Route</button>
                        </form>
                    </div>
                </div>

                <div x-show="showFilters" x-collapse class="w-full grid grid-cols-1 md:grid-cols-2 gap-2 p-4 bg-gray-50 rounded-md shadow-inner mb-6">
                    <select name="sort_by" form="filterForm" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>Sort by ID</option>
                        <option value="source" {{ request('sort_by') == 'source' ? 'selected' : '' }}>Sort by Source</option>
                        <option value="destination_code" {{ request('sort_by') == 'destination_code' ? 'selected' : '' }}>Sort by Destination</option>
                        <option value="distance" {{ request('sort_by') == 'distance' ? 'selected' : '' }}>Sort by Distance</option>
                        <option value="estimated_duration" {{ request('sort_by') == 'estimated_duration' ? 'selected' : '' }}>Sort by Duration</option>
                    </select>
                    <select name="sort_order" form="filterForm" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_order') == 'desc' || !request('sort_order') ? 'selected' : '' }}>Descending</option>
                    </select>
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
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                     <a href="{{ route('admin.routes', array_merge(request()->query(), ['sort_by' => 'id', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center">
                                        ID
                                        @if(request('sort_by') === 'id' || !request('sort_by'))
                                            <span class="ml-1">{{ request('sort_order') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                     <a href="{{ route('admin.routes', array_merge(request()->query(), ['sort_by' => 'source', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center">
                                        Source
                                        @if(request('sort_by') === 'source')
                                            <span class="ml-1">{{ request('sort_order') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                     <a href="{{ route('admin.routes', array_merge(request()->query(), ['sort_by' => 'destination_code', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center">
                                        Destination
                                        @if(request('sort_by') === 'destination_code')
                                            <span class="ml-1">{{ request('sort_order') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                     <a href="{{ route('admin.routes', array_merge(request()->query(), ['sort_by' => 'distance', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center">
                                        Distance
                                        @if(request('sort_by') === 'distance')
                                            <span class="ml-1">{{ request('sort_order') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                     <a href="{{ route('admin.routes', array_merge(request()->query(), ['sort_by' => 'estimated_duration', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center">
                                        Est. Duration
                                        @if(request('sort_by') === 'estimated_duration')
                                            <span class="ml-1">{{ request('sort_order') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($routes as $route)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $route->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $route->source }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $route->destination->city_name ?? $route->destination_code }}
                                    <span class="text-xs text-gray-400">({{ $route->destination_code }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $route->distance }} km</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $route->estimated_duration }} mins</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.routes.edit', $route->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                    <form action="{{ route('admin.routes.delete', $route->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this route?');">
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
                    {{ $routes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
