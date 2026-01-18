@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="{
                    ...datatable({ 
                        url: '/api/admin/destinations',
                        sort_by: '{{ request('sort_by', 'city_name') }}',
                        sort_order: '{{ request('sort_order', 'asc') }}'
                    }),
                    canManageDestinations: {{ Auth::user()->can('manage-destinations') ? 'true' : 'false' }},
                    
                    // Override toggleSelectAll to handle 'code' primary key instead of default 'id'
                    toggleSelectAll() {
                        if (this.items.length > 0 && this.selectedItems.length === this.items.length) {
                            this.selectedItems = [];
                        } else {
                            this.selectedItems = this.items.map(item => item.code);
                        }
                    },
                    
                    bulkDelete() {
                         if (this.selectedItems.length === 0) return;
                         
                         Swal.fire({
                            title: 'Are you sure?',
                            text: 'You won\'t be able to revert this!',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete selected!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Promise.all(this.selectedItems.map(code => 
                                    axios.delete('/api/admin/destinations/' + code)
                                )).then(() => {
                                    Swal.fire('Deleted!', 'Selected destinations have been deleted.', 'success');
                                    this.selectedItems = []; // Clear selection
                                    this.fetchData(this.pagination.current_page);
                                }).catch(err => {
                                    Swal.fire('Error!', 'Something went wrong.', 'error');
                                });
                            }
                        })
                    }
                 }"
            >
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" x-text="canManageDestinations ? 'Manage Destinations' : 'View Destinations'"></h2>
                    </div>
                
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                             <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Search destinations..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                             
                             <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Sort & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Search</button>

                             <!-- Actions Dropdown -->
                             <div class="relative" x-data="{ open: false }" @click.outside="open = false" x-show="canManageDestinations">
                                <button type="button" @click="open = !open" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 flex items-center h-[42px] transition-colors shadow-sm font-medium">
                                    Actions
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200">
                                    <a href="#" 
                                       @click.prevent="if(selectedItems.length === 1) window.location.href = '/admin/destinations/' + selectedItems[0] + '/edit'"
                                       :class="{'text-gray-400 cursor-not-allowed': selectedItems.length !== 1, 'text-gray-700 hover:bg-gray-100': selectedItems.length === 1}"
                                       class="block px-4 py-2 text-sm w-full text-left">
                                        Edit
                                    </a>
                                    <button type="button" 
                                            @click="bulkDelete(); open = false;"
                                            :disabled="selectedItems.length === 0"
                                            :class="{'text-gray-400 cursor-not-allowed': selectedItems.length === 0, 'text-red-700 hover:bg-red-50': selectedItems.length > 0}"
                                            class="block px-4 py-2 text-sm w-full text-left">
                                        Delete
                                    </button>
                                </div>
                            </div>

                            <button x-show="filters.search || filters.sort_by !== 'city_name'" 
                                    @click="filters.search = ''; filters.sort_by = 'city_name'; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Clear
                            </button>

                            @can('manage-destinations')
                            <form action="{{ route('admin.destinations.create') }}" method="GET" class="ml-auto">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-center border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors">Add Destination</button>
                            </form>
                            @endcan
                        </div>

                        <div x-show="showFilters" x-collapse x-cloak class="overflow-hidden">
                            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select x-model="filters.sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="city_name">City Name</option>
                                    <option value="code">Code</option>
                                    <option value="created_at">Date Created</option>
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
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto relative min-h-[200px]">
                    <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 z-10 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left w-10">
                                    <input type="checkbox" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           @change="toggleSelectAll()" 
                                           :checked="checkAllSelected()">
                                </th>
                                <th @click="sortBy('code')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 w-24">
                                    <div class="flex items-center">
                                        Code
                                        <span x-show="filters.sort_by === 'code'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('city_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        City Name
                                        <span x-show="filters.sort_by === 'city_name'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                       <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="item in items" :key="item.code">
                                <tr class="hover:bg-gray-50" :class="{'bg-blue-50': selectedItems.includes(item.code)}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               :value="item.code" 
                                               @change="toggleSelect(item.code)"
                                               :checked="selectedItems.includes(item.code)">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="item.code"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" x-text="item.city_name"></td>
                                </tr>
                            </template>
                             <tr x-show="items.length === 0 && !loading">
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No destinations found.</td>
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
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </button>
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
