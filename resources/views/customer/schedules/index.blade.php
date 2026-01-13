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
                        <select name="from" id="searchFrom" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
                            <option value="">Semua Lokasi</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->code }}" {{ request('from') == $destination->code ? 'selected' : '' }}>
                                    {{ $destination->city_name }} ({{ $destination->code }})
                                </option>
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
                        <select name="to" id="searchTo" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
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
                        <input type="date" name="date" value="{{ $travelDate }}" min="{{ date('Y-m-d') }}" class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-base">
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
        <div class="lg:hidden mb-4 relative z-30">
            <button type="button" id="openFilterBtn" class="w-full bg-white border border-gray-200 text-blue-600 font-bold py-3 px-4 rounded-lg shadow-sm flex items-center justify-center gap-2 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filter Pencarian
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start relative">
            <!-- Sidebar Filters -->
            <div id="filterSidebar" class="w-full lg:w-1/4 transition-all duration-500 ease-in-out max-h-0 overflow-hidden lg:max-h-none lg:overflow-visible">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
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
                                        <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" 
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-base border-gray-300 rounded-md py-2" 
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
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-base border-gray-300 rounded-md py-2" 
                                            placeholder="500000">
                                    </div>
                                </div>
                                <!-- Button Removed as per request (Auto-apply on enter) -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results List (Spanning 3 columns) -->
            <div class="flex-1 w-full lg:w-3/4 p-4 lg:p-0 pt-0">



                <div id="searchResults">
                    @include('customer.schedules.partials.results')
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterSidebar = document.getElementById('filterSidebar');
        const openFilterBtn = document.getElementById('openFilterBtn');
        const filterForm = document.getElementById('filterForm');

        if(openFilterBtn && filterSidebar) {
            openFilterBtn.addEventListener('click', function() {
                // Determine if currently closed based on class OR inline style
                const isClosed = filterSidebar.classList.contains('max-h-0') || filterSidebar.style.maxHeight === '0px';

                if (isClosed) {
                    // Open it
                    filterSidebar.classList.remove('max-h-0');
                    
                    // Set explicit start values to ensure transition happens
                    filterSidebar.style.maxHeight = '0px';

                    // Use double requestAnimationFrame to ensure browser paints the start state
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            filterSidebar.style.maxHeight = filterSidebar.scrollHeight + "px";
                        });
                    });
                } else {
                    // Close it
                    filterSidebar.style.maxHeight = '0px';
                    
                    // After transition, add back the utility classes to keep it consistent
                    setTimeout(() => {
                        // Only add if we represent closed state
                        if (filterSidebar.style.maxHeight === '0px') {
                            filterSidebar.classList.add('max-h-0');
                            filterSidebar.style.maxHeight = ''; // Clear inline style
                        }
                    }, 500);
                }
            });

            // Cleanup for Desktop View
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) { // lg breakpoint
                    // Reset all inline styles so desktop CSS takes over
                    filterSidebar.style.maxHeight = '';
                    filterSidebar.classList.remove('max-h-0');
                } else {
                    // Re-apply closed state if we sized down and it should be closed? 
                    // For now, let's default to closed if inline styles are missing to avoid broken UI
                    if (filterSidebar.style.maxHeight === '' && !filterSidebar.classList.contains('max-h-0')) {
                        filterSidebar.classList.add('max-h-0');
                    }
                }
            });
        }


        // AJAX Filtering
        if (filterForm) {
            const inputs = filterForm.querySelectorAll('input, select');
            
            // Function to fetch results
            const fetchResults = () => {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                const url = `${filterForm.action}?${params.toString()}`;

                // Update URL without refresh
                window.history.pushState({}, '', url);

                // Add opacity to indicate loading
                const resultsContainer = document.getElementById('searchResults');
                resultsContainer.style.opacity = '0.5';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                    resultsContainer.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsContainer.style.opacity = '1';
                    alert('Gagal memuat hasil pencarian via AJAX.');
                });
            };

            // Attach listeners to all inputs
            inputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    e.preventDefault(); // Prevent form submission if it was a submit button
                    fetchResults();
                });
            });

            // Prevent default form submission
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                fetchResults();
            });
        }

        // Swap Button Logic
        const swapBtn = document.getElementById('swapButton');
        const fromSelect = document.getElementById('searchFrom');
        const toSelect = document.getElementById('searchTo');

        if (swapBtn && fromSelect && toSelect) {
            swapBtn.addEventListener('click', function() {
                const temp = fromSelect.value;
                fromSelect.value = toSelect.value;
                toSelect.value = temp;

                // Animation
                swapBtn.querySelector('svg').style.transform = 'rotate(180deg)';
                setTimeout(() => {
                    swapBtn.querySelector('svg').style.transform = 'rotate(0deg)';
                }, 300);
                swapBtn.querySelector('svg').style.transition = 'transform 0.3s ease';

                // Trigger change to update results
                fromSelect.dispatchEvent(new Event('change'));
            });
        }
    });
</script>
@endsection
