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
                <div class="flex items-center w-full max-w-3xl">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" style="background-color: #16a34a; color: white;">1</div>
                        <span class="text-sm font-bold text-green-700 mt-2">Isi Data</span>
                    </div>
                    <div class="flex-1 mx-4 rounded" style="height: 4px; background-color: #16a34a;"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                        <span class="text-sm font-medium text-blue-600 mt-2">Bayar</span>
                    </div>
                    <div class="flex-1 mx-4 rounded" style="height: 4px; background-color: #e5e7eb;"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">3</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Payment Instructions -->
            <div class="flex-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200">
                        <button onclick="switchTab('va')" id="tab-va" class="flex-1 py-4 text-center text-sm font-semibold text-blue-600 border-b-2 border-blue-600 bg-blue-50 transition-colors">
                            Virtual Account
                        </button>
                        <button onclick="switchTab('qris')" id="tab-qris" class="flex-1 py-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                            QRIS
                        </button>
                    </div>

                    <!-- VA Content -->
                    <div id="content-va" class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">BCA Virtual Account</h3>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/1200px-Bank_Central_Asia.svg.png" alt="BCA" class="h-8">
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-6">
                            <p class="text-sm text-gray-500 mb-1">Nomor Virtual Account</p>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-mono font-bold text-gray-900 tracking-wider">8800 1234 5678 9012</span>
                                <button onclick="copyToClipboard('8800123456789012')" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    Salin
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm text-blue-700">Silakan lakukan pembayaran sebelum <b>{{ \Carbon\Carbon::parse($booking->created_at)->addMinutes(30)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</b> atau pesanan Anda akan dibatalkan otomatis.</p>
                            </div>
                            
                            <div class="text-sm text-gray-600">
                                <p class="font-medium mb-2">Petunjuk Pembayaran:</p>
                                <ol class="list-decimal list-inside space-y-1 pl-2">
                                    <li>Buka aplikasi MCA Mobile atau ATM BCA.</li>
                                    <li>Pilih menu <b>m-Transfer</b> > <b>BCA Virtual Account</b>.</li>
                                    <li>Masukkan nomor Virtual Account: <b>8800 1234 5678 9012</b>.</li>
                                    <li>Periksa detail tagihan Anda, lalu masukkan PIN.</li>
                                    <li>Pembayaran selesai. Tiket akan dikirimkan otomatis.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- QRIS Content -->
                    <div id="content-qris" class="p-6 hidden">
                         <div class="flex flex-col items-center justify-center py-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Scan QRIS</h3>
                            <p class="text-sm text-gray-500 mb-6">Support GoPay, OVO, Dana, ShopeePay, BCA, dll.</p>
                            
                            <div class="w-full max-w-sm mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg flex items-start text-left">
                                    <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm text-blue-700">Silakan lakukan pembayaran sebelum <b>{{ \Carbon\Carbon::parse($booking->created_at)->addMinutes(30)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</b> atau pesanan Anda akan dibatalkan otomatis.</p>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=PAY-DEPART-{{ $booking->id }}" alt="QR Code" class="w-48 h-48 mx-auto">
                            </div>

                            <p class="text-sm text-gray-500 mb-2">Total Pembayaran</p>
                            <p class="text-2xl font-bold text-gray-900 mb-6">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>

                             <button class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh QR Code
                            </button>
                        </div>
                    </div>
                </div>

                <button class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition duration-200 text-lg flex justify-center items-center mb-6">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Saya Sudah Membayar
                </button>
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
    function switchTab(tab) {
        const tabs = ['va', 'qris'];
        
        tabs.forEach(t => {
            const btn = document.getElementById('tab-' + t);
            const content = document.getElementById('content-' + t);
            
            if (t === tab) {
                // Active State
                btn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
                btn.classList.remove('text-gray-500', 'hover:text-gray-700');
                content.classList.remove('hidden');
            } else {
                // Inactive State
                btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
                btn.classList.add('text-gray-500', 'hover:text-gray-700');
                content.classList.add('hidden');
            }
        });
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
