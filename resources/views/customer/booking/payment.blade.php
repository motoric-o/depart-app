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
                <h1 class="text-2xl font-bold text-gray-900">Pembayaran</h1>
            </div>
            
            <div class="flex items-center justify-center">
                <div class="flex items-start w-full max-w-3xl">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" style="background-color: #16a34a; color: white;">1</div>
                        <span class="text-sm font-bold text-green-700 mt-2">Isi Data</span>
                    </div>
                    <div class="flex-1 mx-4 rounded mt-3.5" style="height: 4px; background-color: #16a34a;"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                        <span class="text-sm font-medium text-blue-600 mt-2">Bayar</span>
                    </div>
                    <div class="flex-1 mx-4 rounded mt-3.5" style="height: 4px; background-color: #e5e7eb;"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">3</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Selesai</span>
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
                                        <h3 class="font-bold text-green-800">Tagihan #{{ $loop->iteration }}</h3>
                                        <p class="text-xs text-green-600 mt-1">Kursi: {{ $transaction->tickets->pluck('seat_number')->implode(', ') }}</p>
                                    </div>
                                    <span class="px-2 py-1 bg-green-200 text-green-800 text-xs font-bold rounded uppercase">Lunas</span>
                                </div>
                                
                                <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                                    <div class="bg-white p-3 rounded-full shadow-sm mb-4">
                                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-bold text-green-900 mb-1">Pembayaran Berhasil</h4>
                                    <p class="text-sm text-green-700 mb-4">Terima kasih atas pembayaran Anda.</p>
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
                                        <h3 class="font-bold text-gray-700">Tagihan #{{ $loop->iteration }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">Kursi: {{ $transaction->tickets->pluck('seat_number')->implode(', ') }}</p>
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
                                                    Salin
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div class="bg-blue-50 p-3 rounded-lg flex items-start">
                                                <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <p class="text-xs text-blue-800 leading-relaxed">Bayar sebelum <span class="font-bold">{{ \Carbon\Carbon::parse($booking->created_at)->addHours(24)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</span>.</p>
                                            </div>
                                            
                                            <div class="text-xs text-gray-600">
                                                <ol class="list-decimal list-inside space-y-1 pl-1">
                                                    <li>Menu <strong>m-Transfer > BCA Virtual Account</strong>.</li>
                                                    <li>Masukkan nomor VA.</li>
                                                    <li>Masukkan PIN.</li>
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
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-200 text-sm flex justify-center items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Sudah Bayar
                                        </button>
                                    </form>
                                 </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            <!-- Right: Booking Summary -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Rincian Booking</h3>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-md uppercase">{{ $booking->status }}</span>
                    </div>
                    
                    <div class="text-sm text-gray-500 mb-6">
                        Kode Booking: <span class="font-mono font-bold text-gray-900 text-base block mt-1">{{ $booking->id }}</span>
                    </div>

                    <!-- Route -->
                    <div class="border-t border-gray-100 py-4">
                        <div class="flex flex-col gap-2">
                             <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $booking->schedule->route->sourceDestination->city_name ?? $booking->schedule->route->source }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $booking->schedule->route->destination->city_name ?? $booking->schedule->route->destination_code }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('l, d F Y') }} • {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }}
                        </p>
                    </div>

                    <!-- Passenger -->
                    <div class="border-t border-gray-100 py-4">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Penumpang</h4>
                        @foreach($booking->tickets as $ticket)
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-900">{{ $ticket->passenger_name }}</span>
                                <span class="text-sm font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">Kursi {{ $ticket->seat_number }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Total Tagihan</span>
                            <span class="text-xl font-bold text-orange-500">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
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
        // Extract prefix and suffix? Or just toggle based on ID?
        // Actually we need to toggle between VA and QRIS for the SAME transaction.
        // Input: 'va-TRX123'
        
        const parts = tabId.split('-'); // ['va', 'TRX', '123'] (if id has dashes)
        // Helper: split by first dash
        const type = tabId.substring(0, tabId.indexOf('-')); // 'va' or 'qris'
        const suffix = tabId.substring(tabId.indexOf('-') + 1); // 'TRX-123'
        
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
