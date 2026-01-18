@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Steps -->
        <!-- Header Steps -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="javascript:history.back()" class="mr-4 p-2 bg-white rounded-full shadow-sm text-gray-500 hover:text-gray-700 transition border border-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Payment</h1>
            </div>
            
            <div class="flex items-center justify-center">
                <div class="flex items-start w-full max-w-3xl">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" style="background-color: #16a34a; color: white;">1</div>
                        <span class="text-sm font-bold text-green-700 mt-2">Details</span>
                    </div>
                    <div class="flex-1 mx-4 rounded mt-3.5" style="height: 4px; background-color: #16a34a;"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                        <span class="text-sm font-medium text-blue-600 mt-2">Payment</span>
                    </div>
                    <div class="flex-1 mx-4 rounded mt-3.5" style="height: 4px; background-color: #e5e7eb;"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">3</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Finish</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Payment Instructions -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($booking->transactions as $transaction)
                        @if($transaction->status == 'Success')
                            <!-- Paid State -->
                            <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 overflow-hidden flex flex-col h-full">
                                <div class="bg-green-100 px-6 py-4 border-b border-green-200 flex justify-between items-center shrink-0">
                                    <div>
                                        <h3 class="font-bold text-green-800">Bill #{{ $loop->iteration }}</h3>
                                        <p class="text-xs text-green-600 mt-1">Seat: {{ $transaction->tickets->pluck('seat_number')->implode(', ') }}</p>
                                    </div>
                                    <span class="px-2 py-1 bg-green-200 text-green-800 text-xs font-bold rounded uppercase">Paid</span>
                                </div>
                                
                                <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                                    <div class="bg-white p-3 rounded-full shadow-sm mb-4">
                                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-bold text-green-900 mb-1">Payment Successful</h4>
                                    <p class="text-sm text-green-700 mb-4">Thank you for your payment.</p>
                                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                </div>

                                <div class="p-4 border-t border-green-200 bg-green-50 mt-auto shrink-0">
                                    <button disabled class="w-full bg-green-200 text-green-800 font-bold py-2.5 px-4 rounded-lg shadow-sm cursor-default text-sm flex justify-center items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Sudah Dibayar
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Pending State -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full hover:shadow-md transition-shadow">
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center shrink-0">
                                    <div>
                                        <h3 class="font-bold text-gray-700">Bill #{{ $loop->iteration }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">Seat: {{ $transaction->tickets->pluck('seat_number')->implode(', ') }}</p>
                                    </div>
                                    <span class="text-orange-600 font-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                </div>

                                <!-- Tabs -->
                                <div class="flex border-b border-gray-200 shrink-0">
                                    <button onclick="switchTab('va-{{ $transaction->id }}')" id="tab-va-{{ $transaction->id }}" class="flex-1 py-3 text-center text-xs font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50 transition-colors uppercase tracking-wide">
                                        Virtual Account
                                    </button>
                                    <button onclick="switchTab('qris-{{ $transaction->id }}')" id="tab-qris-{{ $transaction->id }}" class="flex-1 py-3 text-center text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors uppercase tracking-wide">
                                        QRIS
                                    </button>
                                </div>

                                <div class="flex-1 flex flex-col h-[420px]">
                                    <!-- VA Content -->
                                    <div id="content-va-{{ $transaction->id }}" class="p-6 flex-1">
                                        <div class="flex items-center justify-between mb-6">
                                            <h3 class="text-base font-semibold text-gray-900">BCA Virtual Account</h3>
                                            <div class="flex flex-col items-end">
                                                @php $vaNumber = '8800' . rand(1000000000, 9999999999); @endphp
                                                <p class="text-lg font-mono font-bold text-gray-900 tracking-wider">{{ $vaNumber }}</p>
                                                <button onclick="copyToClipboard('{{ $vaNumber }}')" class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center mt-1">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div class="bg-blue-50 p-3 rounded-lg flex items-start">
                                                <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <p class="text-xs text-blue-800 leading-relaxed">Pay before <span class="font-bold">{{ \Carbon\Carbon::parse($booking->created_at)->addHours(24)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</span>.</p>
                                            </div>
                                            
                                            <div class="text-xs text-gray-600">
                                                <ol class="list-decimal list-inside space-y-1 pl-1">
                                                    <li>Menu <strong>m-Transfer > BCA Virtual Account</strong>.</li>
                                                    <li>Enter VA number.</li>
                                                    <li>Enter PIN.</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- QRIS Content -->
                                    <div id="content-qris-{{ $transaction->id }}" class="p-6 hidden flex-1 flex flex-col items-center justify-center">
                                         <h3 class="text-base font-semibold text-gray-900 mb-1">Scan QRIS</h3>
                                         <p class="text-xs text-gray-500 mb-4">GoPay, OVO, Dana, BCA, dll.</p>
                                            
                                        <div class="bg-white p-2 rounded-xl shadow-sm border border-gray-100 mb-4">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PAY-{{ $transaction->id }}" alt="QRIS Code" class="w-32 h-32">
                                        </div>

                                        <p class="text-xs text-gray-500 mb-1">Total</p>
                                        <p class="text-xl font-bold text-gray-900 mb-0">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <!-- Payment Action -->
                                 <div class="p-4 border-t border-gray-100 bg-gray-50 mt-auto shrink-0">
                                    <form action="{{ route('booking.complete', $transaction->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="payment_method" id="payment_method-{{ $transaction->id }}" value="Transfer">
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-200 text-sm flex justify-center items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            I Have Paid
                                        </button>
                                    </form>
                                 </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            <!-- Right: Booking Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Booking Details</h3>
                    </div>
                    
                    <div class="p-6">
                        <!-- Route Info -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900">{{ $booking->schedule->route->sourceDestination->city_name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                <span class="text-sm font-bold text-gray-900">{{ $booking->schedule->route->destination->city_name }}</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('d M Y, H:i') }}</p>
                        </div>

                        <!-- Bus Info -->
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Bus</h4>
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-50 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $booking->schedule->bus->bus_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->schedule->bus->bus_number }} • {{ $booking->schedule->bus->bus_type }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-100 pt-4 space-y-2">
                             <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tickets (x{{ $booking->tickets->count() }})</span>
                                <span class="font-medium text-gray-900">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <!-- Add taxes or fees here if applicable -->
                        </div>

                         <div class="border-t border-gray-100 pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold text-gray-900">Total</span>
                                <span class="text-xl font-bold text-blue-600">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        // tabId e.g. 'va-TRX123' or 'qris-TRX123'
        
        const type = tabId.substring(0, tabId.indexOf('-')); // 'va' or 'qris'
        const suffix = tabId.substring(tabId.indexOf('-') + 1); // 'TRX-123' (Transaction ID)
        
        const otherType = (type === 'va') ? 'qris' : 'va';
        const otherTabId = otherType + '-' + suffix;

        const btn = document.getElementById('tab-' + tabId);
        const content = document.getElementById('content-' + tabId);
        
        const otherBtn = document.getElementById('tab-' + otherTabId);
        const otherContent = document.getElementById('content-' + otherTabId);

        // Active State
        btn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
        btn.classList.remove('text-gray-500', 'hover:text-gray-700');
        content.classList.remove('hidden');

        // Inactive State
        otherBtn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
        otherBtn.classList.add('text-gray-500', 'hover:text-gray-700');
        otherContent.classList.add('hidden');

        // Update Payment Method Input
        const methodInput = document.getElementById('payment_method-' + suffix);
        if (methodInput) {
            methodInput.value = (type === 'va') ? 'Transfer' : 'QRIS';
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Nomor VA berhasil disalin!');
        }).catch(err => {
            console.error('Gagal menyalin: ', err);
        });
    }
</script>
@endpush
