@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">


    <!-- Main Content Section -->
    <!-- Main Content Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Search Bar (Top Position) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form action="{{ route('schedules.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <!-- Preserve Filter Params -->
                @if(request('type'))
                    @foreach((array)request('type') as $type)
                        <input type="hidden" name="type[]" value="{{ $type }}">
                    @endforeach
                @endif
                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                <input type="hidden" name="max_price" value="{{ request('max_price') }}">

                <!-- From -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select name="from" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Semua Lokasi</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}" {{ request('from') == $destination->code ? 'selected' : '' }}>
                                    {{ $destination->city_name }} ({{ $destination->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- To -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ke</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select name="to" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Semua Tujuan</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}" {{ request('to') == $destination->code ? 'selected' : '' }}>
                                    {{ $destination->city_name }} ({{ $destination->code }})
                                </option>
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
                        <input type="date" name="date" value="{{ $travelDate }}" min="{{ date('Y-m-d') }}" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>

                <!-- Button -->
                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full md:w-auto bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-6 inline-flex justify-center items-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <!-- Sidebar Filters -->
            <div id="filterSidebar" class="w-full lg:w-1/4 flex-shrink-0 hidden lg:block fixed lg:static inset-0 z-50 bg-white lg:bg-transparent overflow-y-auto lg:overflow-visible transition-all duration-300">
                <div class="bg-white lg:rounded-lg lg:shadow-sm lg:border border-gray-200 p-6 sticky top-0 lg:top-24 min-h-screen lg:min-h-0">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <button type="button" id="closeFilterBtn" class="lg:hidden text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <h3 class="text-lg font-semibold text-gray-900">Filter</h3>
                        </div>
                        <a href="{{ route('schedules.index', array_merge(request()->only(['from', 'to', 'date']))) }}" class="text-sm text-blue-600 hover:text-blue-800">Reset</a>
                    </div>
                    
                    <form action="{{ route('schedules.index') }}" method="GET" id="filterForm">
                        <!-- Preserve main search params -->
                        <input type="hidden" name="from" value="{{ request('from') }}">
                        <input type="hidden" name="to" value="{{ request('to') }}">
                        <input type="hidden" name="date" value="{{ request('date') }}">

                        <!-- Bus Class Filter -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Kelas Bus</h4>
                            <div class="space-y-2">
                                @foreach($busTypes as $type)
                                    <div class="flex items-center">
                                        <input id="type_{{ $loop->index }}" name="type[]" value="{{ $type }}" type="checkbox" 
                                            {{ in_array($type, (array)request('type')) ? 'checked' : '' }}
                                            onchange="document.getElementById('filterForm').submit()"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="type_{{ $loop->index }}" class="ml-2 block text-sm text-gray-600">
                                            {{ $type }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Rentang Harga</h4>
                            <div class="space-y-4">
                                <div>
                                    <label for="min_price" class="text-xs text-gray-500">Minimum</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" 
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="0">
                                    </div>
                                </div>
                                <div>
                                    <label for="max_price" class="text-xs text-gray-500">Maksimum</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" 
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="500000">
                                    </div>
                                </div>
                                <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded text-sm transition-colors">
                                    Terapkan Harga
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results List (Spanning 3 columns) -->
            <div class="flex-1 w-full lg:w-3/4 p-4 lg:p-0">
                <!-- Mobile Filter Toggle -->
                <button type="button" id="openFilterBtn" class="lg:hidden w-full bg-white border border-gray-200 text-blue-600 font-bold py-3 px-4 rounded-lg mb-4 shadow-sm flex items-center justify-center gap-2 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Filter Pencarian
                </button>


                <div class="flex justify-between items-center mb-6 ">
                    <h3 class="text-xl font-semibold text-gray-800">
                        @if($schedules->isEmpty())
                            Tidak ditemukan jadwal
                        @else
                            Tersedia {{ $schedules->count() }} Jadwal Perjalanan
                        @endif
                    </h3>
                    <div class="text-sm text-gray-500">
                        Tanggal: <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($travelDate)->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>

                @if($schedules->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Maaf, tidak ada jadwal ditemukan</h3>
                        <p class="text-gray-500 max-w-md mx-auto">Coba ubah filter pencarian Anda atau pilih tanggal lain untuk melihat jadwal yang tersedia.</p>
                        <a href="{{ route('schedules.index') }}" class="inline-block mt-6 text-blue-600 hover:text-blue-800 font-medium">Reset Pencarian</a>
                    </div>
                @else
                    <div class="flex flex-col gap-6">
                        @foreach($schedules as $schedule)
                            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border border-gray-200">
                                <div class="flex flex-col md:flex-row">
                                    <!-- Left: Bus Info & Times -->
                                    <div class="p-6 flex-grow flex flex-col md:flex-row items-start md:items-center gap-6">
                                        {{-- Time --}}
                                        <div class="flex flex-col items-center min-w-[120px]">
                                            <div class="text-2xl font-bold text-gray-900">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</div>
                                            <div class="h-8 w-0.5 bg-gray-300 my-1"></div>
                                            <div class="text-lg font-medium text-gray-500">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</div>
                                            <div class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($schedule->estimated_duration)->format('H') }}j {{ \Carbon\Carbon::parse($schedule->estimated_duration)->format('i') }}m</div>
                                        </div>

                                        {{-- Route Info --}}
                                        <div class="flex-grow">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $schedule->bus->bus_number ?? 'Bus' }}
                                                </span>
                                                <span class="text-xs text-gray-500">{{ $schedule->bus->bus_type ?? 'Executive' }}</span>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                                    <span class="text-base font-semibold text-gray-800">
                                                        {{ $schedule->route->sourceDestination->city_name ?? $schedule->route->source }}
                                                    </span>
                                                </div>
                                                <div class="ml-1 border-l-2 border-dashed border-gray-200 pl-4 py-1"></div>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                                    <span class="text-base font-semibold text-gray-800">
                                                        {{ $schedule->route->destination->city_name ?? $schedule->route->destination_code }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Facilities (Optional) --}}
                                        <div class="hidden lg:flex gap-2">
                                            <div class="group relative">
                                                <svg class="w-5 h-5 text-gray-400 hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block w-auto p-2 min-w-max rounded-md shadow-md text-white bg-gray-900 text-xs font-bold transition-opacity duration-300">WiFi</span>
                                            </div>
                                            <div class="group relative">
                                                <svg class="w-5 h-5 text-gray-400 hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                <span class="absolute bottom-full mb-2 hidden group-hover:block w-auto p-2 min-w-max rounded-md shadow-md text-white bg-gray-900 text-xs font-bold transition-opacity duration-300">Charger</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Price & CTA -->
                                    <div class="bg-gray-50 p-6 md:w-64 border-l border-gray-100 flex flex-col justify-center items-center md:items-end">
                                        <div class="text-xs text-gray-500 mb-1">Harga per kursi</div>
                                        <div class="text-2xl font-bold text-blue-600 mb-4">
                                            Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}
                                        </div>
                                        
                                        @php
                                            $availableSeats = $schedule->getAvailableSeats($travelDate);
                                        @endphp

                                        <div class="text-sm {{ $availableSeats > 5 ? 'text-green-600' : 'text-red-500' }} font-medium mb-4">
                                            {{ $availableSeats }} Kursi Sisa
                                        </div>

                                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-colors duration-200">
                                            Pilih
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterSidebar = document.getElementById('filterSidebar');
        const openFilterBtn = document.getElementById('openFilterBtn');
        const closeFilterBtn = document.getElementById('closeFilterBtn');

        if(openFilterBtn && filterSidebar) {
            openFilterBtn.addEventListener('click', function() {
                filterSidebar.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            });
        }

        if(closeFilterBtn && filterSidebar) {
            closeFilterBtn.addEventListener('click', function() {
                filterSidebar.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scrolling
            });
        }
    });
</script>
@endsection
