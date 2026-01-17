@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="usersManager({ 
                    url: '/api/admin/users',
                    sort_by: '{{ request('sort_by', 'id') }}',
                    sort_order: '{{ request('sort_order', 'asc') }}',
                    role: '{{ request('role') }}',
                    currentUserRole: '{{ optional(Auth::user()->accountType)->name ?? 'Unknown' }}',
                    canManageUsers: {{ Auth::user()->can('manage-users') ? 'true' : 'false' }},
                    canManageDrivers: {{ Auth::user()->can('manage-drivers') ? 'true' : 'false' }}
                 })"
            >
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Kembali ke Dashboard</a>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" x-text="canManageUsers ? 'Kelola Pengguna' : 'Lihat Pengguna'"></h2>
                    </div>
                    
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                            <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Cari pengguna..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                            
                            <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Urutkan & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Cari</button>

                            <!-- Actions Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 flex items-center h-[42px] transition-colors shadow-sm font-medium">
                                    Aksi
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200">
                                    <a href="#" 
                                       @click.prevent="if(selectedItems.length === 1) window.location.href = '/admin/users/' + selectedItems[0] + '/edit'"
                                       :class="{'text-gray-400 cursor-not-allowed': selectedItems.length !== 1, 'text-gray-700 hover:bg-gray-100': selectedItems.length === 1}"
                                       class="block px-4 py-2 text-sm w-full text-left">
                                        Edit
                                    </a>
                                    <button type="button" 
                                            @click="bulkDelete()"
                                            :disabled="selectedItems.length === 0"
                                            :class="{'text-gray-400 cursor-not-allowed': selectedItems.length === 0, 'text-red-700 hover:bg-red-50': selectedItems.length > 0}"
                                            class="block px-4 py-2 text-sm w-full text-left">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                            
                            <button x-show="filters.search || filters.sort_by !== 'id' || filters.role" 
                                    @click="filters.search = ''; filters.sort_by = 'id'; filters.role = ''; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Bersihkan
                            </button>
                            
                            @can('manage-users')
                            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors ml-auto">
                                Tambah Pengguna
                            </a>
                            @endcan
                        </div>
                        
                        <div x-show="showFilters" x-collapse x-cloak class="overflow-hidden">
                            <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Peran</label>
                                <select x-model="filters.role" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">Semua Peran</option>
                                    <option value="Customer">Customer (Pelanggan)</option>
                                    <option value="Driver">Driver (Pengemudi)</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urutkan Berdasarkan</label>
                                <select x-model="filters.sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="id">ID</option>
                                    <option value="first_name">Nama</option>
                                    <option value="email">Email</option>
                                    <option value="created_at">Tanggal Bergabung</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                                <select x-model="filters.sort_order" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="asc">Menaik (Ascending)</option>
                                    <option value="desc">Menurun (Descending)</option>
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
                                <th @click="sortBy('first_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Nama
                                        <span x-show="filters.sort_by === 'first_name'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Email
                                        <span x-show="filters.sort_by === 'email'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" >Peran</th>
                                <th @click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Bergabung
                                        <span x-show="filters.sort_by === 'created_at'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="user in items" :key="user.id">
                                <tr :class="{'bg-blue-50': selectedItems.includes(user.id)}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               :value="user.id"
                                               @change="toggleSelect(user.id)"
                                               :checked="selectedItems.includes(user.id)">
                                    </td>
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
                                </tr>
                            </template>
                             <tr x-show="items.length === 0 && !loading">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada pengguna ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4 flex justify-between items-center" x-show="pagination.total > 0">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span x-text="pagination.from"></span> sampai <span x-text="pagination.to"></span> dari <span x-text="pagination.total"></span> hasil
                    </div>
                    <div class="flex space-x-1">
                        <button 
                            @click="fetchData(pagination.current_page - 1)" 
                            :disabled="pagination.current_page <= 1"
                            :class="{'opacity-50 cursor-not-allowed': pagination.current_page <= 1}"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Sebelumnya
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
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
