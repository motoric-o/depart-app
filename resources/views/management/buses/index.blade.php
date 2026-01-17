@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="{
                    ...datatable({ 
                        url: '/api/admin/buses',
                        sort_by: '{{ request('sort_by', 'bus_number') }}',
                        sort_order: '{{ request('sort_order', 'asc') }}'
                    }),
                    canManageBuses: {{ Auth::user()->can('manage-buses') ? 'true' : 'false' }},
                    deleteItem(id, url) {
                        if (!confirm('Are you sure you want to delete this bus?')) return;
                        
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Remove item from list
                                this.items = this.items.filter(item => item.id !== id);
                                alert(data.message);
                            } else {
                                alert('Failed to delete item.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred.');
                        });
                    }
                 }"
            >
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" x-text="canManageBuses ? 'Manage Buses' : 'View Buses'"></h2>
                    </div>
                
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                            <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Search buses..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                            
                            <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Sort & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Search</button>
                            
                            <button x-show="filters.search || filters.sort_by !== 'bus_number'" 
                                    @click="filters.search = ''; filters.sort_by = 'bus_number'; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Clear
                            </button>

                            @can('manage-buses')
                            <form action="{{ route('admin.buses.create') }}" method="GET" class="ml-auto">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-center border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors">Add Bus</button>
                            </form>
                            @endcan
                        </div>

                        <div x-show="showFilters" x-collapse style="display: none;" class="w-full grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select x-model="filters.sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="bus_number">Bus Number</option>
                                    <option value="bus_name">Bus Name</option>
                                    <option value="bus_type">Type</option>
                                    <option value="capacity">Capacity</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                <select x-model="filters.sort_order" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="asc">Ascending</option>
                                    <option value="desc">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto relative min-h-[200px]">
                    <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 z-10 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th @click="sortBy('bus_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Bus Number
                                        <span x-show="filters.sort_by === 'bus_number'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('bus_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Bus Name
                                        <span x-show="filters.sort_by === 'bus_name'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('bus_type')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Type
                                        <span x-show="filters.sort_by === 'bus_type'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('capacity')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Capacity
                                        <span x-show="filters.sort_by === 'capacity'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layout</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                       <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="bus in items" :key="bus.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="bus.bus_number"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.bus_name || '-'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.bus_type"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.capacity"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.seat_rows + ' x ' + bus.seat_columns"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <template x-if="canManageBuses">
                                            <div>
                                                <a :href="'/admin/buses/' + bus.id + '/edit'" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                <button @click="deleteItem(bus.id, '/admin/buses/' + bus.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                            </div>
                                        </template>
                                        <template x-if="!canManageBuses">
                                            <span class="text-gray-400 cursor-not-allowed">Restricted</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                             <tr x-show="items.length === 0 && !loading">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No buses found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-between items-center" x-show="pagination.total > 0">
                    <div class="text-sm text-gray-700">
                        Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results
                    </div>
                    <div class="flex space-x-2">
                        <button 
                            @click="fetchData(pagination.current_page - 1)" 
                            :disabled="pagination.current_page <= 1"
                            :class="{'opacity-50 cursor-not-allowed': pagination.current_page <= 1}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </button>
                        <button 
                            @click="fetchData(pagination.current_page + 1)" 
                            :disabled="pagination.current_page >= pagination.last_page"
                            :class="{'opacity-50 cursor-not-allowed': pagination.current_page >= pagination.last_page}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
