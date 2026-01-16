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
                    
                    canManageUsers: {{ Auth::user()->can('manage-users') ? 'true' : 'false' }},
                    canManageDrivers: {{ Auth::user()->can('manage-drivers') ? 'true' : 'false' }},

                    canManage(targetRole) {
                        // Priority 1: High level user management
                        if (this.canManageUsers) {
                            if (!targetRole) return false;
                            const allowed = ['Customer', 'Driver'];
                            // Owner/Super Admin logic handled by Gate mostly, but refining for target hierarchy
                            if (this.currentUserRole === 'Owner' || this.currentUserRole === 'Super Admin') { 
                                 if (this.currentUserRole === 'Owner') allowed.push('Admin', 'Super Admin', 'Financial Admin', 'Operations Admin', 'Scheduling Admin');
                                 else if (this.currentUserRole === 'Super Admin') allowed.push('Admin', 'Financial Admin', 'Operations Admin', 'Scheduling Admin');
                            }
                            return allowed.includes(targetRole);
                        }

                        // Priority 2: Ops Admin managing Drivers
                        if (this.canManageDrivers && targetRole === 'Driver') {
                            return true;
                        }

                        return false;
                    },
                    
                    deleteItem(id, url) {
                        if (!confirm('Are you sure you want to delete this user?')) return;
                        
                        fetch(url, {
                            method: 'POST', // Using POST with _method DELETE inside logic if needed, but here URL is specific /delete which maps to controller method that handles it. controller method uses delete? valid method is POST to that route? 
                            // Route definition: Route::delete('/admin/users/{id}/delete', ...)? No, standard resource or explicit?
                            // Let's check api.php or web.php. Web routes usually match method.
                            // Route::delete('users/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
                            // So we need DELETE method.
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
                                // Optional: Show success message/toast
                                alert(data.message);
                            } else {
                                alert('Failed to delete item.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred.');
                        });
                    },
                    
                    formatDate(dateString) {
                         const options = { month: 'short', day: 'numeric', year: 'numeric' };
                         return new Date(dateString).toLocaleDateString('en-US', options);
                    },
                    
                    roleClass(roleName) {
                        switch(roleName) {
                            case 'Admin': 
                            case 'Super Admin':
                            case 'Financial Admin':
                            case 'Operations Admin':
                            case 'Scheduling Admin':
                                return 'bg-purple-100 text-purple-800';
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

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" x-text="canManageUsers ? 'Manage Users' : 'View Users'"></h2>
                    </div>
                    
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                            <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Search users..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                            
                            <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Sort & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Search</button>
                            
                            <button x-show="filters.search || filters.sort_by !== 'id' || filters.role" 
                                    @click="filters.search = ''; filters.sort_by = 'id'; filters.role = ''; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Clear
                            </button>
                            
                            @can('manage-users')
                            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors ml-auto">
                                Add Customer
                            </a>
                            @endcan
                        </div>
                        
                        <div x-show="showFilters" x-collapse style="display: none;" class="w-full grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select x-model="filters.role" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">All Roles</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Driver">Driver</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select x-model="filters.sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="id">ID</option>
                                    <option value="first_name">Name</option>
                                    <option value="email">Email</option>
                                    <option value="created_at">Date Joined</option>
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
                                                <button @click="deleteItem(user.id, '/admin/users/' + user.id)" class="text-red-600 hover:text-red-900">Delete</button>
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
