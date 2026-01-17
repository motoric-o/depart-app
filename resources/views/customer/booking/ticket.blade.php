@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
            <div class="flex items-center self-start sm:self-auto">
                <a href="{{ route('booking.history') }}" class="p-2 bg-white rounded-full shadow-sm text-gray-500 hover:text-gray-700 transition border border-gray-200 mr-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">E-Tiket</h1>
            </div>
            <button onclick="window.print()" class="flex items-center text-blue-600 hover:text-blue-700 font-medium self-end sm:self-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Tiket
            </button>
        </div>

        <!-- Ticket Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
            <!-- Ticket Header -->
            <div class="bg-blue-600 px-6 sm:px-8 py-6 text-white relative overflow-hidden">
                    <div>
                        <p class="text-blue-100 text-sm font-medium uppercase tracking-wider mb-1">Bus Operator</p>
                        <h2 class="text-2xl font-bold truncate" title="{{ $booking->schedule->bus ? $booking->schedule->bus->bus_name : 'Bus Not Assigned' }}">{{ $booking->schedule->bus ? ($booking->schedule->bus->bus_name ?? 'Bus Not Assigned') : 'Bus Not Assigned' }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                             <p class="text-blue-100 text-sm opacity-90">{{ $booking->schedule->bus ? ($booking->schedule->bus->bus_number . ' • ' . $booking->schedule->bus->bus_type) : 'Pending Assignment' }}</p>
                        </div>
                        <div class="mt-2 text-blue-100 text-xs opacity-80 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Driver: {{ $booking->schedule->driver ? ($booking->schedule->driver->first_name . ' ' . $booking->schedule->driver->last_name) : 'Pending Assignment' }}
                        </div>
                    </div>
                    <div class="text-left sm:text-right space-y-3">
                        <div>
                            <p class="text-blue-100 text-sm font-medium uppercase tracking-wider mb-1">Booking ID</p>
                            <h2 class="text-xl font-mono font-bold tracking-wide leading-none">{{ $booking->id }}</h2>
                        </div>
                        <div>
                             <p class="text-blue-100 text-xs font-medium uppercase tracking-wider mb-0.5 opacity-80">Schedule ID</p>
                             <p class="font-mono font-semibold text-sm">{{ $booking->schedule->id }}</p>
                        </div>
                        @if($booking->schedule->remarks)
                        <div>
                             <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-400 text-blue-900 shadow-sm box-decoration-clone">
                                {{ $booking->schedule->remarks }}
                             </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ticket Body -->
            <div class="px-6 sm:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Column 1 & 2: Trip Info -->
                    <div class="md:col-span-2 space-y-8">
                        <!-- Departure -->
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-4 h-4 bg-blue-600 rounded-full ring-4 ring-blue-100"></div>
                                <div class="w-0.5 flex-1 bg-gray-200 my-1"></div>
                            </div>
                            <div class="pb-8">
                                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Berangkat</p>
                                <h4 class="text-xl font-bold text-gray-900">{{ $booking->schedule->route ? ($booking->schedule->route->sourceDestination->city_name ?? $booking->schedule->route->source) : ($booking->schedule->route_source ?? 'N/A') }}</h4>
                                <p class="text-gray-600 font-medium mt-1">
                                    {{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('l, d F Y') }}
                                    <span class="mx-2 text-gray-300">|</span>
                                    <span class="text-blue-600 font-bold">{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Arrival -->
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-4 h-4 border-4 border-orange-500 bg-white rounded-full"></div>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Tiba</p>
                                <h4 class="text-xl font-bold text-gray-900">{{ $booking->schedule->route ? ($booking->schedule->route->destination->city_name ?? $booking->schedule->route->destination_code) : ($booking->schedule->route_destination ?? 'N/A') }}</h4>
                                <p class="text-gray-600 font-medium mt-1">
                                    {{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('l, d F Y') }}
                                    <span class="mx-2 text-gray-300">|</span> 
                                    <span class="text-orange-600 font-bold">{{ \Carbon\Carbon::parse($booking->schedule->arrival_time)->format('H:i') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: QR Code -->
                    <div class="flex flex-col items-center justify-center border-t md:border-t-0 md:border-l border-gray-100 pt-8 md:pt-0 md:pl-8">
                         <div class="bg-white p-2 rounded-xl border border-gray-200 shadow-sm mb-2">
                             <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $booking->id }}" alt="QR Code" class="w-32 h-32">
                         </div>
                         <p class="text-xs text-gray-400 text-center">Scan untuk Verifikasi</p>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 my-8"></div>

                <!-- Passenger Details -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Detail Penumpang
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($booking->tickets as $ticket)
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex justify-between items-center group hover:border-blue-200 transition-colors">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Nama Penumpang</p>
                                    <p class="font-bold text-gray-900 text-lg">{{ $ticket->passenger_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 mb-1">Nomor Kursi</p>
                                    <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-lg font-bold shadow-sm shadow-blue-200">
                                        {{ $ticket->seat_number }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Footer decoration -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">Terima kasih telah menggunakan layanan kami. Semoga selamat sampai tujuan.</p>
            </div>
        </div>
    </div>
</div>
@endsection
