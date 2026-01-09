@extends('layouts.app')

@section('content')
<div class="relative pb-32 overflow-hidden">
    <!-- Background Pattern/Image -->
    <div class="absolute inset-0">
        <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1570125909232-eb263c188f7e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Bus Travel">
    </div>

    <!-- Hero Content -->
    <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8 flex flex-col items-center text-center font-roboto">
        <div class="bg-black bg-opacity-60 p-10 rounded-2xl backdrop-blur-sm max-w-4xl">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-6 shadow-sm">
                Partner Perjalanan Bus Terpercaya
            </h1>
            <p class="mt-6 text-xl text-gray-100 max-w-3xl mx-auto">
                Pesan tiket bus antar kota dan provinsi dengan mudah, aman, dan tanpa biaya tambahan. Nikmati perjalanan nyaman dengan armada terbaik kami.
            </p>
        </div>
    </div>
</div>

<!-- Search Widget Section -->
<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24">
    <div class="bg-white rounded-lg shadow-xl p-6 md:p-8">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-700 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Cari Tiket Bus
            </h2>
        </div>
        
        <form action="#" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Origin -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dari</label>
                    <div class="flex items-center border rounded-md p-3 bg-gray-50">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <input type="text" placeholder="Jakarta" class="bg-transparent w-full outline-none text-gray-700 font-semibold placeholder-gray-400">
                    </div>
                </div>

                <!-- Destination -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Ke</label>
                    <div class="flex items-center border rounded-md p-3 bg-gray-50">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <input type="text" placeholder="Surabaya" class="bg-transparent w-full outline-none text-gray-700 font-semibold placeholder-gray-400">
                    </div>
                </div>

                <!-- Date -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Pergi Tanggal</label>
                    <div class="flex items-center border rounded-md p-3 bg-gray-50">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <input type="date" class="bg-transparent w-full outline-none text-gray-700 font-semibold">
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button type="button" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-md shadow-lg transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari Bus
                    </button>
                </div>
            </div>
            
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span class="mr-4 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Jaminan Harga Resmi
                </span>
                <span class="mr-4 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Pasti Dapat Kursi
                </span>
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Reschedule Mudah
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Features Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="p-6">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Harga Terbaik</h3>
            <p class="text-gray-600">Dapatkan harga tiket termurah dengan berbagai promo menarik setiap harinya.</p>
        </div>
        <div class="p-6">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Transaksi Aman</h3>
            <p class="text-gray-600">Sistem pembayaran yang aman dan terpercaya dengan berbagai metode pembayaran.</p>
        </div>
        <div class="p-6">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Layanan 24/7</h3>
            <p class="text-gray-600">Tim customer service kami siap membantu perjalanan Anda kapanpun dibutuhkan.</p>
        </div>
    </div>
</div>

<!-- Popular Destinations -->
<div class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center">Rute Populer</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1555899434-94d1368aa7af?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Jakarta - Bandung" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Jakarta - Bandung</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 85.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1574614674724-4f9384724d1a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Surabaya - Malang" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Surabaya - Malang</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 45.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1596405631246-64d5d4148cd8?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Yogyakarta - Semarang" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Yogyakarta - Semarang</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 60.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1605218427335-3a4dd8846012?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Bali - Surabaya" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Bali - Surabaya</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 250.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
