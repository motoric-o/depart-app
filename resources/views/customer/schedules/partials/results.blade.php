<div class="flex justify-between items-center mb-6 ">
    <h3 class="text-xl font-semibold text-gray-800">
        @if($schedules->isEmpty())
            No schedules found
        @else
            Available {{ $schedules->count() }} Trip Schedules
        @endif
    </h3>
    <div class="text-sm text-gray-500">
        Date: <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($travelDate)->translatedFormat('l, d F Y') }}</span>
    </div>
</div>

@if($schedules->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Sorry, no schedules found</h3>
        <p class="text-gray-500 max-w-md mx-auto">Try changing your search filters or choose another date to see available schedules.</p>
        <a href="{{ route('schedules.index') }}" class="inline-block mt-6 text-blue-600 hover:text-blue-800 font-medium">Reset Search</a>
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
                        <div class="text-xs text-gray-500 mb-1">Price per seat</div>
                        <div class="text-2xl font-bold text-blue-600 mb-4">
                            Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}
                        </div>
                        
                        @php
                            $availableSeats = $schedule->getAvailableSeats($travelDate);
                        @endphp

                        <div class="text-sm {{ $availableSeats > 5 ? 'text-green-600' : 'text-red-500' }} font-medium mb-4">
                            {{ $availableSeats }} Seats Left
                        </div>

                        <a href="{{ route('booking.create', ['schedule_id' => $schedule->id, 'date' => $travelDate]) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-colors duration-200 text-center block">
                            Select
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
