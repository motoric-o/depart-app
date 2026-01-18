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
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'You will not be able to revert this!',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#2563EB',
                            cancelButtonColor: '#4B5563',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                               this.performDelete([id], url);
                            }
                        })
                    },

                    bulkDelete() {
                        if (this.selectedItems.length === 0) return;
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: `You are about to delete ${this.selectedItems.length} buses. This action cannot be undone!`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#2563EB',
                            cancelButtonColor: '#4B5563',
                            confirmButtonText: 'Yes, delete them!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const promises = this.selectedItems.map(id => {
                                    return fetch(`/admin/buses/${id}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                            'Accept': 'application/json'
                                        }
                                    }).then(res => res.json());
                                });

                                Promise.all(promises).then(results => {
                                    const failed = results.filter(r => !r.success);
                                    if (failed.length === 0) {
                                        this.items = this.items.filter(item => !this.selectedItems.includes(item.id));
                                        this.selectedItems = [];
                                        Swal.fire('Deleted!', 'Selected buses have been deleted.', 'success');
                                    } else {
                                        Swal.fire('Failed!', `${failed.length} items failed to delete.`, 'error');
                                    }
                                }).catch(err => {
                                    console.error(err);
                                    Swal.fire('Failed!', 'An error occurred during bulk deletion.', 'error');
                                });
                            }
                        })
                    },

                    performDelete(ids, url) {
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
                                this.items = this.items.filter(item => item.id !== ids[0]);
                                Swal.fire('Deleted!', data.message, 'success');
                            } else {
                                Swal.fire('Failed!', 'Failed to delete item.', 'error');
                            }
                        })
                        .catch(err => {
                             console.error(err);
                             Swal.fire('Failed!', 'An error occurred.', 'error');
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

                            <!-- Actions Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false" x-show="canManageBuses">
                                <button type="button" @click="open = !open" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 flex items-center h-[42px] transition-colors shadow-sm font-medium">
                                    Actions
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200">
                                    <a href="#" 
                                       @click.prevent="if(selectedItems.length === 1) window.location.href = '/admin/buses/' + selectedItems[0] + '/edit'"
                                       :class="{'text-gray-400 cursor-not-allowed': selectedItems.length !== 1, 'text-gray-700 hover:bg-gray-100': selectedItems.length === 1}"
                                       class="block px-4 py-2 text-sm w-full text-left">
                                        Edit
                                    </a>
                                    <button type="button" 
                                            @click="bulkDelete()"
                                            :disabled="selectedItems.length === 0"
                                            :class="{'text-gray-400 cursor-not-allowed': selectedItems.length === 0, 'text-red-700 hover:bg-red-50': selectedItems.length > 0}"
                                            class="block px-4 py-2 text-sm w-full text-left">
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
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

                        <div x-show="showFilters" x-collapse x-cloak class="overflow-hidden">
                            <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
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

                <!-- Alerts handled by global App layout -->

                <div class="overflow-x-auto relative min-h-[200px]">
                    <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 z-10 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                            <tr>
                                <th class="px-6 py-3 text-left w-10" x-show="canManageBuses">
                                    <input type="checkbox" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           @change="toggleSelectAll()"
                                           :checked="checkAllSelected()">
                                </th>
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
                            </tr>
                        </thead>
                       <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="bus in items" :key="bus.id">
                                <tr :class="{'bg-blue-50': selectedItems.includes(bus.id)}">
                                    <td class="px-6 py-4 whitespace-nowrap" x-show="canManageBuses">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               :value="bus.id"
                                               @change="toggleSelect(bus.id)"
                                               :checked="selectedItems.includes(bus.id)">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="bus.bus_number"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.bus_name || '-'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.bus_type"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.capacity"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="bus.seat_rows + ' x ' + bus.seat_columns"></td>
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
