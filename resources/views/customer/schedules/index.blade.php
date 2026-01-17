@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" 
     x-data='scheduleSearch(@json($schedules))'>

    <!-- Main Content Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Search Bar (Top Position) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form action="{{ route('schedules.index') }}" method="GET" @submit.prevent="fetchResults" class="flex flex-col md:flex-row gap-4 items-end">
                
                <!-- From -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select name="from" x-model="filters.from" id="searchFrom" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                            <option value="">Semua Lokasi</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}">
                                    {{ $destination->city_name }} ({{ $destination->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Swap Button -->
                <div class="hidden md:flex items-center justify-center mb-1">
                    <button type="button" @click="swapLocations" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-blue-600 transition-colors" title="Tukar Lokasi">
                        <svg class="w-6 h-6 transform transition-transform duration-300" :class="{'rotate-180': isSwapped}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </button>
                </div>

                <!-- To -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ke</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <select name="to" x-model="filters.to" id="searchTo" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                            <option value="">Semua Tujuan</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}">
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
                        <input type="date" name="date" x-model="filters.date" min="{{ date('Y-m-d') }}" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                    </div>
                </div>

                <!-- Button -->
                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full md:w-auto bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-8 inline-flex justify-center items-center text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Mobile Filter Toggle -->
        <div class="lg:hidden mb-4 relative z-30" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 text-blue-600 font-bold py-3 px-4 rounded-lg shadow-sm flex items-center justify-center gap-2 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filter Pencarian
            </button>
            <div x-show="open" x-collapse class="mt-4">
                 <!-- Mobile Filter Content could go here, or simple toggle visibility of the sidebar below -->
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start relative">
            <!-- Sidebar Filters -->
            <div id="filterSidebar" class="w-full lg:w-1/4 max-h-0 overflow-hidden lg:max-h-none lg:overflow-visible transition-all duration-300 lg:block" :class="{'max-h-[1000px]': $data.openMobileFilters, 'max-h-0': !$data.openMobileFilters && window.innerWidth < 1024}">
                 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900">Filter</h3>
                        </div>
                        <button type="button" @click="resetFilters" class="text-sm text-blue-600 hover:text-blue-800">Reset</button>
                    </div>
                    
                    <form @submit.prevent="fetchResults">
                        <!-- Bus Class Filter -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Kelas Bus</h4>
                            <div class="space-y-2">
                                @foreach($busTypes as $type)
                                    <div class="flex items-center">
                                        <input id="type_{{ $loop->index }}" 
                                               value="{{ $type }}" 
                                               type="checkbox" 
                                               x-model="filters.type"
                                               class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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
                                        <input type="number" x-model="filters.min_price" id="min_price" placeholder="0"
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-base border-gray-300 rounded-md py-2">
                                    </div>
                                </div>
                                <div>
                                    <label for="max_price" class="text-xs text-gray-500">Maksimum</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" x-model="filters.max_price" id="max_price" placeholder="500000"
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-base border-gray-300 rounded-md py-2">
                                    </div>
                                </div>
                            </div>
                            <!-- Apply Button -->
                            <div class="mt-6">
                                <button type="submit" class="w-full bg-blue-600 text-white font-medium py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results List (Spanning 3 columns) -->
            <div class="flex-1 w-full lg:w-3/4 p-4 lg:p-0 pt-0 relative min-h-[400px]">
                
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">
                        <span x-text="schedules.length === 0 ? 'Tidak ditemukan jadwal' : 'Tersedia ' + schedules.length + ' Jadwal Perjalanan'"></span>
                    </h3>
                    <div class="text-sm text-gray-500">
                        Tanggal: <span class="font-medium text-gray-700" x-text="formatDateHeader(filters.date)"></span>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 z-10 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && schedules.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Maaf, tidak ada jadwal ditemukan</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Coba ubah filter pencarian Anda atau pilih tanggal lain untuk melihat jadwal yang tersedia.</p>
                    <button @click="resetFilters" class="inline-block mt-6 text-blue-600 hover:text-blue-800 font-medium">Reset Pencarian</button>
                </div>

                <!-- List -->
                <div x-show="!loading && schedules.length > 0" class="flex flex-col gap-6">
                    <template x-for="schedule in schedules" :key="schedule.id">
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border border-gray-200">
                            <div class="flex flex-col md:flex-row">
                                <!-- Left: Bus Info & Times -->
                                <div class="p-6 grow flex flex-col md:flex-row items-start md:items-center gap-6">
                                    {{-- Time --}}
                                    <div class="flex flex-col items-center min-w-[120px]">
                                        <div class="text-2xl font-bold text-gray-900" x-text="schedule.departure_format"></div>
                                        <div class="h-8 w-0.5 bg-gray-300 my-1"></div>
                                        <div class="text-lg font-medium text-gray-500" x-text="schedule.arrival_format"></div>
                                        <div class="text-xs text-gray-400 mt-1" x-text="schedule.duration_hour + 'j ' + schedule.duration_minute + 'm'"></div>
                                    </div>

                                    {{-- Route Info --}}
                                    <div class="grow">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="schedule.bus.bus_number || 'Bus'"></span>
                                            <span class="text-xs text-gray-500" x-text="schedule.bus.bus_type || 'Executive'"></span>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                                <span class="text-base font-semibold text-gray-800" x-text="schedule.route.source"></span>
                                            </div>
                                            <div class="ml-1 border-l-2 border-dashed border-gray-200 pl-4 py-1"></div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                                <span class="text-base font-semibold text-gray-800" x-text="schedule.route.destination"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bookmark Button --}}
                                    <button @click="toggleBookmark(schedule)" class="absolute top-4 right-4 md:static md:top-auto md:right-auto text-gray-400 hover:text-red-500 transition-colors focus:outline-none" :class="{'text-red-500': schedule.is_bookmarked}">
                                        <svg class="w-6 h-6" :fill="schedule.is_bookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                    </button>

                                    {{-- Facilities (Static for now) --}}
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
                                    <div class="text-2xl font-bold text-blue-600 mb-4" x-text="'Rp ' + schedule.formatted_price"></div>
                                    
                                    <div class="text-sm font-medium mb-4" 
                                         :class="schedule.available_seats > 5 ? 'text-green-600' : 'text-red-500'"
                                         x-text="schedule.available_seats + ' Kursi Sisa'">
                                    </div>

                                    <a :href="'/booking/create?schedule_id=' + schedule.id + '&date=' + filters.date" 
                                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-colors duration-200 text-center block">
                                        Pilih
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function scheduleSearch(initialSchedules) {
        return {
            schedules: initialSchedules,
            loading: false,
            isSwapped: false,
            openMobileFilters: false, // For mobile sidebar linkage
            filters: {
                from: @json(request("from", "")),
                to: @json(request("to", "")),
                date: @json($travelDate ?? ""),
                min_price: @json(request("min_price", "")),
                max_price: @json(request("max_price", "")),
                type: @json(request("type", []))
            },
            
            async fetchResults() {
                this.loading = true;
                
                // Build Query String
                const params = new URLSearchParams();
                if(this.filters.from) params.append('from', this.filters.from);
                if(this.filters.to) params.append('to', this.filters.to);
                if(this.filters.date) params.append('date', this.filters.date);
                if(this.filters.min_price) params.append('min_price', this.filters.min_price);
                if(this.filters.max_price) params.append('max_price', this.filters.max_price);
                
                // Handle array for type
                this.filters.type.forEach(t => params.append('type[]', t));

                const queryString = params.toString();
                // Update URL
                window.history.pushState({}, '', '?' + queryString);
                
                try {
                    // Call the API endpoint using correct named route
                    const apiUrl = '{{ route("api.schedules.search") }}';
                    const fullUrl = `${apiUrl}?${queryString}`;
                    console.log('Fetching:', fullUrl);
                    
                    const res = await fetch(fullUrl);
                    if (!res.ok) throw new Error('API Error: ' + res.status);
                    
                    const data = await res.json();
                    console.log('Search Results:', data);
                    this.schedules = data;
                    
                } catch (e) {
                    console.error('Fetch error:', e);
                    alert('Gagal memuat jadwal. Silakan coba lagi.');
                } finally {
                    this.loading = false;
                    // Close mobile filters if open
                    this.openMobileFilters = false;
                }
            },
            
            swapLocations() {
                this.isSwapped = !this.isSwapped;
                const temp = this.filters.from;
                this.filters.from = this.filters.to;
                this.filters.to = temp;
                // Optional: Auto fetch? 
                // this.fetchResults(); // User plan says "Explicit Apply", but swap usually implies immediate intent?
                // Let's stick to "Explicit" or implicit if user swaps. Usually swap doesn't need "Apply".
                // I'll leave it manual for now to be safe, or just let them click Search.
            },

            resetFilters() {
                this.filters.from = '';
                this.filters.to = '';
                this.filters.min_price = '';
                this.filters.max_price = '';
                this.filters.type = [];
                // Date usually stays? Or reset to today?
                // this.filters.date = '{{ date("Y-m-d") }}'; 
                this.fetchResults();
            },

            formatDateHeader(dateStr) {
                if(!dateStr) return 'Semua';
                const date = new Date(dateStr);
                return date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            async toggleBookmark(schedule) {
                // Optimistic UI Update
                schedule.is_bookmarked = !schedule.is_bookmarked;

                try {
                    const res = await fetch('{{ route("bookmarks.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            bookmarkable_id: schedule.id,
                            bookmarkable_type: 'App\\Models\\Schedule'
                        })
                    });

                    if (!res.ok) throw new Error('Failed to toggle bookmark');
                    
                    // Optional: Show toast notification
                } catch (e) {
                    console.error(e);
                    // Revert UI if failed
                    schedule.is_bookmarked = !schedule.is_bookmarked;
                    alert('Gagal mengubah status bookmark. Silakan coba lagi.');
                }
            }
        }
    }
</script>
@endsection
