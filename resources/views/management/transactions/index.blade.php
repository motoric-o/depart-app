@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="transactionsManager({ 
                    url: '{{ route('admin.transactions') }}',
                    sort_by: 'transaction_date',
                    sort_order: 'desc',
                    filters: {
                        date: '{{ request('date') }}',
                        status: '{{ request('status') }}',
                        search: ''
                    }
                 })"
                 x-init="fetchData(1)"
            >
                <div class="mb-4">
                    <a href="{{ route('admin.financial.reports') }}" class="text-gray-600 hover:text-gray-900">&larr; Kembali ke Laporan</a>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Transaksi</h2>
                    </div>
                
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false, openExport: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                             <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Cari ID atau Pelanggan..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                             
                             <button type="button" @click="showFilters = !showFilters" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 flex items-center justify-center border border-transparent h-[42px] whitespace-nowrap transition-colors">
                                <span>Urutkan & Filter</span>
                                <svg x-show="!showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="showFilters" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            
                            <button type="button" @click="fetchData(1)" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent h-[42px] font-medium transition-colors">Cari</button>

                            <!-- Export Dropdown -->
                            <div class="relative" @click.outside="openExport = false">
                                <button type="button" @click="openExport = !openExport" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 flex items-center h-[42px] transition-colors shadow-sm font-medium">
                                    Ekspor / Cetak
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="openExport" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200">
                                    <a href="{{ route('admin.transactions.export') }}" class="block px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-100">
                                        Unduh CSV
                                    </a>
                                    <button onclick="window.print()" class="block px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-100">
                                        Cetak Tabel
                                    </button>
                                </div>
                            </div>
                            
                            <button x-show="filters.search || filters.date || filters.status || filters.sort_by !== 'transaction_date'" 
                                    @click="filters.search = ''; filters.date = ''; filters.status = ''; filters.sort_by = 'transaction_date'; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Bersihkan
                            </button>
                        </div>

                        <div x-show="showFilters" x-collapse x-cloak class="overflow-hidden">
                            <div class="w-full grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urutkan Berdasarkan</label>
                                <select x-model="filters.sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="transaction_date">Tanggal</option>
                                    <option value="id">ID</option>
                                    <option value="total_amount">Jumlah</option>
                                    <option value="status">Status</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                                <select x-model="filters.sort_order" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="asc">Menaik (Ascending)</option>
                                    <option value="desc">Menurun (Descending)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select x-model="filters.status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">Semua Status</option>
                                    <option value="Success">Sukses</option>
                                    <option value="Pending">Menunggu</option>
                                    <option value="Failed">Gagal</option>
                                    <option value="Payment Issue">Masalah Pembayaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <input type="date" x-model="filters.date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                            </div>
                        </div>
                    </div>
                </div>

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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th @click="sortBy('transaction_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Tanggal
                                        <span x-show="filters.sort_by === 'transaction_date'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref Pemesanan</th> 
                                <th @click="sortBy('total_amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Jumlah
                                        <span x-show="filters.sort_by === 'total_amount'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Status
                                        <span x-show="filters.sort_by === 'status'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="transaction in items" :key="transaction.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="'#' + transaction.id"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <span x-text="transaction.account ? transaction.account.first_name + ' ' + (transaction.account.last_name || '') : 'Tamu'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(transaction.transaction_date)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span x-text="transaction.booking_id ? '#' + transaction.booking_id : '-'" :class="{'text-gray-400': !transaction.booking_id}"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900" x-text="formatPrice(transaction.total_amount)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer"
                                              :class="{
                                                  'bg-green-100 text-green-800': transaction.status === 'Success',
                                                  'bg-yellow-100 text-yellow-800': transaction.status === 'Pending' || transaction.status === 'Pending Confirmation',
                                                  'bg-red-100 text-red-800': transaction.status === 'Failed' || transaction.status === 'Payment Issue'
                                              }"
                                              @click="transaction.status === 'Payment Issue' ? viewProofs(transaction) : null"
                                              x-text="transaction.status">
                                        </span>
                                        <span x-show="transaction.status === 'Payment Issue'" class="ml-2 text-xs text-blue-600 hover:underline cursor-pointer" @click="viewProofs(transaction)">(Lihat Bukti)</span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0 && !loading">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada transaksi ditemukan.</td>
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

                <!-- Proof Modal -->
                <div x-show="showProofModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showProofModal" @click="showProofModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="showProofModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Bukti Masalah Pembayaran</h3>
                                        <div class="mt-4">
                                            <template x-if="selectedProofs.length === 0">
                                                <p class="text-sm text-gray-500">Tidak ada bukti ditemukan.</p>
                                            </template>
                                            <template x-for="proof in selectedProofs" :key="proof.id">
                                                <div class="mb-4 border-b border-gray-200 pb-4">
                                                    <p class="text-sm font-medium text-gray-800 mb-1" x-text="proof.sender_type === 'driver' ? 'Pesan Sopir:' : 'Pesan Admin:'"></p>
                                                    <p class="text-sm text-gray-600 italic mb-2" x-text='proof.message'></p>
                                                    <template x-if="proof.file_path">
                                                        <a :href="'/storage/' + proof.file_path" target="_blank" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            Lihat Berkas
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <form :action="'/admin/transactions/' + selectedTransactionId + '/resolve'" method="POST" class="w-full sm:w-auto sm:ml-3">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                                        Selesaikan Pembayaran
                                    </button>
                                </form>
                                <button type="button" @click="showProofModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
