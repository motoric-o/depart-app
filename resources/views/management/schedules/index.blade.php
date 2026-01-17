@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="{ 
                    ...datatable({ 
                        url: '/api/admin/schedules',
                        sort_by: '{{ request('sort_by', 'departure_time') }}',
                        sort_order: '{{ request('sort_order', 'desc') }}',
                        filter_category: '{{ request('filter_category') }}'
                    }),
                    canManageSchedules: {{ Auth::user()->can('manage-schedules') ? 'true' : 'false' }},
                    formatDate(dateString) {
                         const options = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
                         return new Date(dateString).toLocaleDateString('en-US', options);
                    },
                    formatPrice(price) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
                    }
                 }"
            >
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" x-text="canManageSchedules ? 'Manage Schedules' : 'View Schedules'"></h2>
                    </div>
                
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                             <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Search schedules..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                             
                             <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Sort & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Search</button>

                            <!-- Actions Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 flex items-center h-[42px] transition-colors shadow-sm font-medium">
                                    Actions
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200">
                                    <template x-if="selectedItems.length === 1">
                                        <a :href="'/admin/schedules/' + selectedItems[0] + '/details'" 
                                           class="block px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-100">
                                            Details
                                        </a>
                                    </template>
                                     <a href="#" 
                                       @click.prevent="if(selectedItems.length === 1) window.location.href = '/admin/schedules/' + selectedItems[0] + '/edit'"
                                       :class="{'text-gray-400 cursor-not-allowed': selectedItems.length !== 1, 'text-gray-700 hover:bg-gray-100': selectedItems.length === 1}"
                                       class="block px-4 py-2 text-sm w-full text-left">
                                        Edit
                                    </a>
                                </div>
                            </div>
                            
                            <button x-show="filters.search || filters.sort_by !== 'departure_time'" 
                                    @click="filters.search = ''; filters.sort_by = 'departure_time'; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Clear
                            </button>

                            @can('manage-schedules')
                            <form action="{{ route('admin.schedules.create') }}" method="GET" class="ml-auto">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-center border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors">Add Schedule</button>
                            </form>
                            @endcan
                        </div>

                        <div x-show="showFilters" x-collapse x-cloak class="overflow-hidden">
                            <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select x-model="filters.sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="departure_time">Departure</option>
                                    <option value="arrival_time">Arrival</option>
                                    <option value="price_per_seat">Price</option>
                                    <option value="remarks">Remarks</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                <select x-model="filters.sort_order" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="asc">Ascending</option>
                                    <option value="desc">Descending</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
                                <select x-model="filters.filter_category" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">All Schedules</option>
                                    <option value="incomplete">Requires Attention</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts handled by global App layout -->

                <div class="overflow-x-auto relative min-h-[200px]">
                    <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 z-10 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                            <tr>
                                <th class="px-6 py-3 text-left w-10">
                                    <input type="checkbox" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           @change="toggleSelectAll()"
                                           :checked="checkAllSelected()">
                                </th>
                                <th @click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        ID
                                        <span x-show="filters.sort_by === 'id'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th @click="sortBy('bus_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Bus
                                        <span x-show="filters.sort_by === 'bus_id'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th> 
                                <th @click="sortBy('departure_time')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Departure
                                        <span x-show="filters.sort_by === 'departure_time'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('arrival_time')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Arrival
                                        <span x-show="filters.sort_by === 'arrival_time'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('price_per_seat')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Price
                                        <span x-show="filters.sort_by === 'price_per_seat'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('remarks')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Remarks
                                        <span x-show="filters.sort_by === 'remarks'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="schedule in items" :key="schedule.id">
                                <tr :class="{'bg-blue-50': selectedItems.includes(schedule.id)}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               :value="schedule.id"
                                               @change="toggleSelect(schedule.id)"
                                               :checked="selectedItems.includes(schedule.id)">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="schedule.id"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="(schedule.route && schedule.route.destination) ? schedule.route.source + ' -> ' + schedule.route.destination.city_name : (schedule.route_source ? schedule.route_source + ' -> ' + schedule.route_destination : 'N/A')"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="schedule.bus ? schedule.bus.bus_number : 'N/A'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="schedule.driver ? (schedule.driver.first_name + ' ' + (schedule.driver.last_name || '')) : 'Unassigned'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(schedule.departure_time)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(schedule.arrival_time)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatPrice(schedule.price_per_seat)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span x-text="schedule.remarks"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0 && !loading">
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">No schedules found.</td>
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
                    <div class="flex space-x-1">
                        <button 
                            @click="fetchData(pagination.current_page - 1)" 
                            :disabled="pagination.current_page <= 1"
                            :class="{'opacity-50 cursor-not-allowed': pagination.current_page <= 1}"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </button>

                        <template x-for="page in getPages()">
                            <button 
                                @click="page !== '...' ? fetchData(page) : null" 
                                :class="{
                                    'bg-blue-600 text-white border-blue-600': pagination.current_page === page, 
                                    'bg-white text-gray-700 hover:bg-gray-50 border-gray-300': pagination.current_page !== page,
                                    'cursor-default': page === '...'
                                }" 
                                :disabled="page === '...'"
                                x-text="page" 
                                class="px-3 py-1 border rounded-md text-sm font-medium transition-colors">
                            </button>
                        </template>

                        <button 
                            @click="fetchData(pagination.current_page + 1)" 
                            :disabled="pagination.current_page >= pagination.last_page"
                            :class="{'opacity-50 cursor-not-allowed': pagination.current_page >= pagination.last_page}"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
