@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="expensesManager({ 
                    url: '/api/admin/expenses',
                    current_user_id: {{ json_encode(Auth::id()) }},
                    sort_by: '{{ request('sort_by', 'created_at') }}',
                    sort_order: '{{ request('sort_order', 'desc') }}',
                    filters: {
                        type: '{{ request('type') }}',
                        status: '{{ request('status') }}',
                        date_from: '{{ request('date_from') }}'
                    }
                 })"
            >
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Manage Expenses</h2>
                    </div>
                    
                    <!-- Toolbar -->
                    <div class="w-full" x-data="{ showFilters: false }">
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mb-2">
                            <input type="text" x-model="filters.search" @keydown.enter="fetchData(1)" placeholder="Search expenses..." class="grow border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 h-[42px]">
                            
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
                                    @can('approve-expense')
                                    <!-- Pending Actions -->
                                    <div x-show="selectionCommonStatus === 'Pending'">
                                        <button type="button" 
                                                @click="bulkAction('verify', 'In Process')"
                                                class="block px-4 py-2 text-sm w-full text-left text-green-700 hover:bg-green-50">
                                            Approve Selected
                                        </button>
                                        <button type="button" 
                                                @click="bulkAction('verify', 'Rejected')"
                                                class="block px-4 py-2 text-sm w-full text-left text-red-700 hover:bg-red-50">
                                            Reject Selected
                                        </button>
                                    </div>

                                    <!-- In Process Actions -->
                                    <div x-show="selectionCommonStatus === 'In Process'">
                                        <button type="button" 
                                                @click="bulkAction('verify', 'Pending Confirmation')"
                                                class="block px-4 py-2 text-sm w-full text-left text-blue-700 hover:bg-blue-50">
                                            Pay Selected
                                        </button>
                                        <button type="button" 
                                                @click="bulkAction('verify', 'Canceled')"
                                                class="block px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-50">
                                            Cancel Selected
                                        </button>
                                    </div>
                                    
                                    <!-- Payment Issue Actions -->
                                    <div x-show="selectionCommonStatus === 'Payment Issue'">
                                        <button type="button" 
                                                @click="bulkAction('verify', 'Pending Confirmation')"
                                                class="block px-4 py-2 text-sm w-full text-left text-green-700 hover:bg-green-50">
                                            Resolve (Re-process)
                                        </button>
                                        <button type="button" 
                                                @click="bulkAction('verify', 'Rejected')"
                                                class="block px-4 py-2 text-sm w-full text-left text-red-700 hover:bg-red-50">
                                            Reject
                                        </button>
                                    </div>
                                    
                                    <!-- Fallback / Empty State -->
                                    <div x-show="!['Pending', 'In Process', 'Payment Issue'].includes(selectionCommonStatus)" class="px-4 py-2 text-sm text-gray-400 italic">
                                        <span x-show="selectedItems.length === 0">No items selected</span>
                                        <span x-show="selectedItems.length > 0 && selectionCommonStatus === 'mixed'">Mixed status selection</span>
                                        <span x-show="selectedItems.length > 0 && selectionCommonStatus !== 'mixed' && !['Pending', 'In Process', 'Payment Issue'].includes(selectionCommonStatus)">No actions available</span>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                            
                            <button x-show="filters.search || filters.sort_by !== 'date' || filters.status || filters.type" 
                                    @click="filters.search = ''; filters.sort_by = 'date'; filters.status = ''; filters.type = ''; filters.date_from = ''; fetchData(1)" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center justify-center border border-transparent h-[42px] transition-colors"
                                    style="display: none;">
                                Clear
                            </button>
                            
                            @can('create-expense')
                            <a href="{{ route('admin.expenses.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 border border-transparent flex items-center justify-center h-[42px] whitespace-nowrap font-medium transition-colors ml-auto">
                                Record New Expense
                            </a>
                            @endcan
                        </div>
                        
                        <div x-show="showFilters" x-collapse x-cloak class="overflow-hidden">
                            <div class="w-full grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-md shadow-inner mb-6 mt-4 border border-gray-200">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select x-model="filters.type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                        <option value="">All Types</option>
                                        <option value="reimbursement">Reimbursement</option>
                                        <option value="operational">Operational</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="salary">Salary</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select x-model="filters.status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                        <option value="">All Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="In Process">In Process</option>
                                        <option value="Pending Confirmation">Pending Confirmation</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Payment Issue">Payment Issue</option>
                                        <option value="Rejected">Rejected</option>
                                        <option value="Canceled">Canceled</option>
                                        <option value="Failed">Failed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                    <input type="date" x-model="filters.date_from" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
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
                </div>

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
                                <th @click="sortBy('date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Date
                                        <span x-show="filters.sort_by === 'date'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('description')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Description
                                        <span x-show="filters.sort_by === 'description'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('type')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Type
                                        <span x-show="filters.sort_by === 'type'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Amount
                                        <span x-show="filters.sort_by === 'amount'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100">
                                    <div class="flex items-center">
                                        Status
                                        <span x-show="filters.sort_by === 'status'" class="ml-1" x-text="filters.sort_order === 'asc' ? '↑' : '↓'"></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="expense in items" :key="expense.id">
                                <tr :class="{'bg-blue-50': selectedItems.includes(expense.id)}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               :value="expense.id"
                                               @change="toggleSelect(expense.id)"
                                               :checked="selectedItems.includes(expense.id)">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(expense.date)"></td>
                                    <td class="px-6 py-4 text-sm text-gray-900" x-text="expense.description"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="typeClass(expense.type)" 
                                              x-text="expense.type">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatMoney(expense.amount)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="statusClass(expense.status)"
                                            x-text="expense.status">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <!-- Check for Payment Issue Proof first -->
                                        <template x-if="expense.transaction && (expense.transaction.payment_issue_proofs || expense.transaction.paymentIssueProofs) && (expense.transaction.payment_issue_proofs || expense.transaction.paymentIssueProofs).length > 0">
                                            <div>
                                                <button type="button" 
                                                        @click="openIssueModal(expense)"
                                                        class="text-red-600 hover:text-red-900 hover:underline font-bold text-left">
                                                    Review Issue
                                                </button>
                                                <template x-if="expense.proof_file">
                                                     <div class="mt-1">
                                                        <button type="button" @click="openReceiptModal(expense)" class="text-xs text-gray-500 hover:text-gray-700 hover:underline">Original Receipt</button>
                                                     </div>
                                                </template>
                                            </div>
                                        </template>
                                        
                                        <!-- Fallback to standard proof if no issue proof -->
                                        <template x-if="!expense.transaction || !(expense.transaction.payment_issue_proofs || expense.transaction.paymentIssueProofs) || (expense.transaction.payment_issue_proofs || expense.transaction.paymentIssueProofs).length === 0">
                                            <div>
                                                <!-- Confirm Receipt Button -->
                                                <template x-if="expense.status === 'Paid' && expense.account_id == current_user_id">
                                                    <button type="button" 
                                                            @click="confirmExpense(expense.id)"
                                                            class="text-green-600 hover:text-green-900 hover:underline font-bold block mb-1">
                                                        Confirm Receipt
                                                    </button>
                                                </template>

                                                <template x-if="expense.proof_file">
                                                    <button type="button" @click="openReceiptModal(expense)" class="text-blue-600 hover:text-blue-900 hover:underline">View Receipt</button>
                                                </template>
                                                <template x-if="!expense.proof_file">
                                                    <span class="text-gray-400" x-show="!(expense.status === 'Paid' && expense.account_id == current_user_id)">-</span>
                                                </template>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                             <tr x-show="items.length === 0 && !loading">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No expenses found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                        <!-- Review Issue Modal -->
                        <div x-show="issueModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="issueModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="closeIssueModal()"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                
                                <div x-show="issueModalOpen" x-transition.scale class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 sm:mt-0 sm:ml-0 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Review Payment Issue
                                                </h3>
                                                <div class="mt-4" x-show="activeIssue">
                                                    <p class="text-sm text-gray-500 font-bold">Driver Message:</p>
                                                    <p class="text-sm text-gray-700 mb-4 bg-gray-50 p-2 rounded" x-text="activeIssue.message || 'No message provided'"></p>
                                                    
                                                    <p class="text-sm text-gray-500 font-bold mb-2">Proof File:</p>
                                                    <template x-if="activeIssue.file_path">
                                                        <div class="w-full">
                                                            <template x-if="isPdf(activeIssue.file_path)">
                                                                <iframe :src="'/storage/' + activeIssue.file_path" class="w-full h-[80vh] rounded border" frameborder="0"></iframe>
                                                            </template>
                                                            <template x-if="!isPdf(activeIssue.file_path)">
                                                                <img :src="'/storage/' + activeIssue.file_path" class="w-full h-auto rounded border" alt="Issue Proof">
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="!activeIssue.file_path">
                                                        <p class="text-gray-400 italic">No file uploaded</p>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="button" 
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                                                @click="verifyExpense(activeExpense.id, 'Pending Confirmation'); closeIssueModal()">
                                            Resolve (Re-process)
                                        </button>
                                        <button type="button" 
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                                @click="verifyExpense(activeExpense.id, 'Rejected'); closeIssueModal()">
                                            Reject
                                        </button>
                                        
                                        <a x-show="activeIssue && activeIssue.file_path" 
                                           :href="'/storage/' + activeIssue.file_path" 
                                           target="_blank"
                                           class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-blue-50 text-base font-medium text-blue-700 hover:bg-blue-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Open in New Tab
                                        </a>

                                        <button type="button" 
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                                @click="closeIssueModal()">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Original Receipt Modal -->
                        <div x-show="receiptModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="receipt-modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="receiptModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="closeReceiptModal()"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                
                                <div x-show="receiptModalOpen" x-transition.scale class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 sm:mt-0 sm:ml-0 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="receipt-modal-title">
                                                    Original Receipt
                                                </h3>
                                                <div class="mt-4" x-show="activeExpense">
                                                    <template x-if="activeExpense.proof_file">
                                                        <div class="w-full">
                                                            <template x-if="isPdf(activeExpense.proof_file)">
                                                                <iframe :src="'/storage/' + activeExpense.proof_file" class="w-full h-[80vh] rounded border" frameborder="0"></iframe>
                                                            </template>
                                                            <template x-if="!isPdf(activeExpense.proof_file)">
                                                                <img :src="'/storage/' + activeExpense.proof_file" class="w-full h-auto rounded border" alt="Receipt">
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <a x-show="activeExpense && activeExpense.proof_file" 
                                            :href="'/storage/' + activeExpense.proof_file" 
                                            target="_blank"
                                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-blue-50 text-base font-medium text-blue-700 hover:bg-blue-100 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                             Open in New Tab
                                         </a>
                                         
                                        <button type="button" 
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                                @click="closeReceiptModal()">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-between items-center" x-show="pagination.total > 0">
                    <div class="text-sm text-gray-700">
                        Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results
                    </div>
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