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
        <form action="{{ route('schedules.index') }}" method="GET" class="bg-white p-6 md:p-8 rounded-lg shadow-sm border border-gray-200">
            <div class="mb-6 border-b border-gray-100 pb-4">
                <h2 class="text-2xl font-bold text-gray-800">Cari Jadwal & Pesan Tiket</h2>
                <p class="text-gray-500 mt-1">Temukan perjalanan bus terbaik antar kota dengan harga resmi.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-6 md:items-end">
                <!-- From -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select name="from" id="from" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                            <option value="">Semua Lokasi</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}">{{ $destination->city_name }} ({{ $destination->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Swap Button -->
                <div class="hidden md:flex items-center justify-center mb-1">
                    <button type="button" id="swapButton" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-blue-600 transition-colors" title="Tukar Lokasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </button>
                </div>

                <!-- To -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ke</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select name="to" id="to" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                            <option value="">Semua Tujuan</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}">{{ $destination->city_name }} ({{ $destination->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Date -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="date" name="date" min="{{ date('Y-m-d') }}" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                    </div>
                </div>

                <!-- Button -->
                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full md:w-auto bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-8 inline-flex justify-center items-center text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari Bus
                    </button>
                </div>
            </div>

            <div class="mt-6 flex items-center text-sm text-gray-500">
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
                <img src="https://images.unsplash.com/photo-1611638281871-1063d3e76e1f?q=80&w=2033&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Jakarta - Bandung" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Jakarta - Bandung</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 85.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1721879223016-96dbd4952ffd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8bWFsYW5nfGVufDB8fDB8fHww" alt="Surabaya - Malang" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Surabaya - Malang</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 45.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1652100591395-6d512bfaf5bb?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8c2VtYXJhbmd8ZW58MHx8MHx8fDA%3D" alt="Yogyakarta - Semarang" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Yogyakarta - Semarang</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 60.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://plus.unsplash.com/premium_photo-1690959214934-802fdf410b3e?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8c3VyYWJheWF8ZW58MHx8MHx8fDA%3D" alt="Bali - Surabaya" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">Bali - Surabaya</h3>
                    <p class="text-sm text-gray-500 mb-3">Mulai dari Rp 250.000</p>
                    <a href="#" class="text-blue-600 font-semibold text-sm hover:underline">Lihat Jadwal →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-3xl font-extrabold text-gray-900 mb-12 text-center">Apa Kata Penumpang</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
        <!-- Testimonial 1 -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 hover:-translate-y-1 transition duration-300">
            <div class="flex items-center mb-4">
                <img class="h-11 w-11 rounded-full object-cover" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Budi Santoso">
                <div class="ml-4">
                    <h4 class="text-lg font-bold text-gray-900">Budi Santoso</h4>
                    <div class="flex text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    </div>
                </div>
            </div>
            <p class="text-gray-600 italic">"Sangat praktis pesan tiket di sini. Tidak perlu antri di terminal, harga juga transparan tanpa biaya tersembunyi. Sangat direkomendasikan!"</p>
        </div>

        <!-- Testimonial 2 -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 hover:-translate-y-1 transition duration-300">
            <div class="flex items-center mb-4">
                <img class="h-11 w-11 rounded-full object-cover" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Siti Rahayu">
                <div class="ml-4">
                    <h4 class="text-lg font-bold text-gray-900">Siti Rahayu</h4>
                    <div class="flex text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    </div>
                </div>
            </div>
            <p class="text-gray-600 italic">"Reschedule jadwal sangat mudah. CS-nya juga ramah dan fast respon. Pengalaman perjalanan jadi lebih tenang."</p>
        </div>

        <!-- Testimonial 3 -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 hover:-translate-y-1 transition duration-300">
            <div class="flex items-center mb-4">
                <img class="h-11 w-11 rounded-full object-cover" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Rizky Pratama">
                <div class="ml-4">
                    <h4 class="text-lg font-bold text-gray-900">Rizky Pratama</h4>
                    <div class="flex text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    </div>
                </div>
            </div>
            <p class="text-gray-600 italic">"Banyak pilihan PO bus dan rute. Harganya bersaing banget dibanding aplikasi lain. Top markotop!"</p>
        </div>
    </div>
</div>

<!-- Trusted Partners Section -->
<div class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center">Mitra Resmi Kami</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 items-center opacity-70 grayscale hover:grayscale-0 transition duration-500">
             <div class="flex justify-center items-center h-16 bg-white rounded-lg shadow-sm hover:shadow-md transition"><span class="text-lg font-bold text-gray-400">Pahala Kencana</span></div>
             <div class="flex justify-center items-center h-16 bg-white rounded-lg shadow-sm hover:shadow-md transition"><span class="text-lg font-bold text-gray-400">Lorena</span></div>
             <div class="flex justify-center items-center h-16 bg-white rounded-lg shadow-sm hover:shadow-md transition"><span class="text-lg font-bold text-gray-400">Rosalia Indah</span></div>
             <div class="flex justify-center items-center h-16 bg-white rounded-lg shadow-sm hover:shadow-md transition"><span class="text-lg font-bold text-gray-400">Sinar Jaya</span></div>
             <div class="flex justify-center items-center h-16 bg-white rounded-lg shadow-sm hover:shadow-md transition"><span class="text-lg font-bold text-gray-400">Damri</span></div>
             <div class="flex justify-center items-center h-16 bg-white rounded-lg shadow-sm hover:shadow-md transition"><span class="text-lg font-bold text-gray-400">Gunung Harta</span></div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-3xl font-extrabold text-gray-900 mb-12 text-center">Pertanyaan Umum</h2>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 divide-y divide-gray-200">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Bagaimana cara memesan tiket?</h3>
            <p class="mt-2 text-gray-600">Pilih rute keberangkatan, tujuan, dan tanggal perjalanan. Kemudian pilih kursi yang tersedia dan lakukan pembayaran.</p>
        </div>
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Apakah bisa refund tiket?</h3>
            <p class="mt-2 text-gray-600">Ya, Anda dapat mengajukan refund maksimal 24 jam sebelum keberangkatan dengan potongan biaya administrasi sebesar 25%.</p>
        </div>
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Metode pembayaran apa saja yang tersedia?</h3>
            <p class="mt-2 text-gray-600">Kami menerima pembayaran via Transfer Bank, E-Wallet (OVO, Dana, GoPay), dan Minimarket (Indomaret/Alfamart).</p>
        </div>
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Apakah harus cetak tiket?</h3>
            <p class="mt-2 text-gray-600">Tidak perlu. Anda cukup menunjukkan E-Ticket yang dikirimkan ke email atau aplikasi saat boarding.</p>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<div class="bg-blue-600 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl font-extrabold mb-4">Dapatkan Diskon Spesial!</h2>
        <p class="text-lg mb-8 text-blue-100">Berlangganan newsletter kami untuk info promo dan rute terbaru.</p>
        <div class="max-w-md mx-auto flex flex-col sm:flex-row gap-2">
            <input type="email" placeholder="Masukkan email Anda" class="w-full flex-1 px-4 py-3 rounded-md text-gray-900 bg-white border-2 border-transparent outline-none placeholder-gray-500 transition-all">
            <button class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-md transition duration-200 shadow-md">Langganan</button>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const swapBtn = document.getElementById('swapButton');
        const fromSelect = document.getElementById('from');
        const toSelect = document.getElementById('to');

        if (swapBtn && fromSelect && toSelect) {
            swapBtn.addEventListener('click', function() {
                const temp = fromSelect.value;
                fromSelect.value = toSelect.value;
                toSelect.value = temp;
                
                // Add a small animation effect
                swapBtn.querySelector('svg').style.transform = 'rotate(180deg)';
                setTimeout(() => {
                    swapBtn.querySelector('svg').style.transform = 'rotate(0deg)';
                }, 300);
            });
            swapBtn.querySelector('svg').style.transition = 'transform 0.3s ease';
        }
    });
</script>
