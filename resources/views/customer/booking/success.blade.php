@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Steps -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Pemesanan Berhasil</h1>
            <div class="flex items-center justify-center">
                <div class="flex items-start w-full max-w-3xl">
                    <!-- Step 1: Done -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" style="background-color: #16a34a; color: white;">1</div>
                        <span class="text-sm font-bold text-green-700 mt-2">Isi Data</span>
                    </div>
                    <div class="flex-1 mx-4 rounded mt-3.5" style="height: 4px; background-color: #16a34a;"></div>
                    
                    <!-- Step 2: Done -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" style="background-color: #16a34a; color: white;">2</div>
                        <span class="text-sm font-bold text-green-700 mt-2">Bayar</span>
                    </div>
                    <div class="flex-1 mx-4 rounded mt-3.5" style="height: 4px; background-color: #16a34a;"></div>
                    
                    <!-- Step 3: Active -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">3</div>
                        <span class="text-sm font-medium text-blue-600 mt-2">Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h2>
            <p class="text-gray-500 mb-8">Tiket Anda telah terbit dan dikirim ke email Anda.</p>
            
            <a href="{{ route('booking.history') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                Lihat Tiket Saya
            </a>
        </div>
    </div>
</div>
@endsection
