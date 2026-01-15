@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="{ 
                    ...datatable({ 
                        url: '/api/admin/users',
                        sort_by: '{{ request('sort_by', 'id') }}',
                        sort_order: '{{ request('sort_order', 'asc') }}',
                        role: '{{ request('role') }}'
                    }),
                    currentUserRole: '{{ Auth::user()->accountType->name }}',
                    
                    canManage(targetRole) {
                        if (!targetRole) return false;
                        const allowed = ['Customer', 'Driver'];
                        if (this.currentUserRole === 'Owner') {
                            allowed.push('Admin');
                        }
                        return allowed.includes(targetRole);
                    },

                    formatDate(dateString) {
                         const options = { month: 'short', day: 'numeric', year: 'numeric' };
                         return new Date(dateString).toLocaleDateString('en-US', options);
                    },
                    
                    roleClass(roleName) {
                        switch(roleName) {
                            case 'Admin': return 'bg-purple-100 text-purple-800';
                            case 'Owner': return 'bg-yellow-100 text-yellow-800';
                            case 'Driver': return 'bg-blue-100 text-blue-800';
                            default: return 'bg-green-100 text-green-800';
                        }
                    }
                 }"
            >
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>
                <div class="mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
                    <h2 class="text-2xl font-bold">Manage Users</h2>
                    <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 w-full md:w-auto">
                        
                        <!-- Filter Form -->
                        <div class="w-full" x-data="{ showFilters: false }">
                            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                                <input type="text" x-model="filters.search" placeholder="Search users..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                                <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap">
                                    <span>Sort & Filter</span>
                                    <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px]">Search</button>
                                <button x-show="filters.search || filters.sort_by !== 'id' || filters.role" @click="filters.search = ''; filters.sort_by = 'id'; filters.role = ''; fetchData(1)" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-center flex items-center justify-center border border-transparent h-[42px]">Clear</button>
                            </div>
                            
                            <div x-show="showFilters" x-collapse class="w-full grid grid-cols-1 md:grid-cols-3 gap-2 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4">
                                <select x-model="filters.role" @change="fetchData(1)" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">All Roles</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Driver">Driver</option>
                                    <option value="Admin">Admin</option>
                                </select>
                                <select x-model="filters.sort_by" @change="fetchData(1)" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="id">Sort by ID</option>
                                    <option value="first_name">Sort by Name</option>
                                    <option value="email">Sort by Email</option>
                                    <option value="created_at">Sort by Joined</option>
                                </select>
                                <select x-model="filters.sort_order" @change="fetchData(1)" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="asc">Ascending</option>
                                    <option value="desc">Descending</option>
                                </select>
                            </div>
                        </div>

                        <form action="{{ route('admin.users.create') }}" method="GET">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 text-center border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap">Add Customer</button>
                        </form>
                    </div>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
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
                                <th @click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        ID
                                        <span x-show="filters.sort_by === 'id'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('first_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Name
                                        <span x-show="filters.sort_by === 'first_name'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Email
                                        <span x-show="filters.sort_by === 'email'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" >Role</th>
                                <th @click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Joined
                                        <span x-show="filters.sort_by === 'created_at'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="user in items" :key="user.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="user.id"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="user.first_name + ' ' + (user.last_name || '')"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="user.email"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="roleClass((user.account_type) ? user.account_type.name : 'Unknown')" 
                                              x-text="(user.account_type) ? user.account_type.name : 'Unknown'">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(user.created_at)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <template x-if="canManage((user.account_type) ? user.account_type.name : '')">
                                            <div>
                                                <a :href="'/admin/users/' + user.id + '/edit'" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                <form :action="'/admin/users/' + user.id + '/delete'" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </div>
                                        </template>
                                        <template x-if="!canManage((user.account_type) ? user.account_type.name : '')">
                                            <span class="text-gray-400 cursor-not-allowed">Restricted</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                             <tr x-show="items.length === 0 && !loading">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td>
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
