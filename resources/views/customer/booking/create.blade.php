@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Steps -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Pemesanan Tiket</h1>
            <div class="flex items-center justify-center">
                <div class="flex items-start w-full max-w-3xl">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">1</div>
                        <span class="text-sm font-medium text-blue-600 mt-2">Isi Data</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-300 mx-4 mt-3.5"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">2</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Bayar</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-300 mx-4 mt-3.5"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">3</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="font-medium text-red-600">Whoops! Ada masalah dengan input Anda.</div>
                <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Forms -->
            <form action="{{ route('booking.store') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                <input type="hidden" name="travel_date" value="{{ $travelDate ?: request('date') }}">
                <!-- Login Alert -->
                @guest
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-900">Masuk untuk kemudahan pemesanan</h4>
                            <p class="text-sm text-blue-700 mt-1">Simpan data penumpang dan nikmati promo khusus member.</p>
                            <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 mt-2 inline-block">Masuk / Daftar &rarr;</a>
                        </div>
                    </div>
                @endguest

                <!-- Contact Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Data Pemesan
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}">
                                <span class="text-xs text-gray-500 mt-1">Sesuai KTP/SIM/Paspor</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Handphone</label>
                                <input type="tel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Contoh: 08123456789">
                                <span class="text-xs text-gray-500 mt-1">E-ticket akan dikirim via WhatsApp</span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ auth()->user()->email ?? '' }}">
                                <span class="text-xs text-gray-500 mt-1">E-ticket akan dikirim ke alamat email ini</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Passenger Details (Hidden) -->
                <input type="hidden" name="passenger_name" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}">

                <!-- Seat Selection -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        Pilih Kursi
                    </h3>
                    
                    <div class="flex flex-col items-center">
                        <!-- Driver Position -->
                        <div class="w-full max-w-xs flex justify-end mb-6 pr-2">
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-8 h-8 rounded-full border-2 border-gray-400 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">Sopir</span>
                            </div>
                        </div>

                        <!-- Seats Grid -->
                        <div class="bg-gray-100 p-6 rounded-2xl border border-gray-200 overflow-x-auto">
                            <div class="grid gap-y-3 gap-x-6 w-max mx-auto" style="grid-template-columns: repeat(2, 3rem) 2rem repeat(2, 3rem);">
                                @php $rows = $schedule->bus->seat_rows ?? 8; @endphp
                                @for($r = 1; $r <= $rows; $r++)
                                    <!-- Left Side (A, B) -->
                                    @foreach(['A', 'B'] as $col)
                                        @php 
                                            // Handle potential whitespace issues
                                            $seatNo = trim($r . $col); 
                                            $isOccupied = in_array($seatNo, $occupiedSeats ?? []);
                                        @endphp

                                        @if($isOccupied)
                                            <div class="w-12 h-12 rounded-lg border-2 bg-gray-100 border-gray-200 flex flex-col items-center justify-center cursor-not-allowed relative">
                                                <span class="text-sm font-bold text-gray-300">{{ $seatNo }}</span>
                                                <div class="absolute inset-0 flex items-center justify-center text-lg font-bold text-gray-400 select-none">X</div>
                                            </div>
                                        @else
                                            <label class="relative group cursor-pointer group">
                                                <input type="radio" name="seat_number" value="{{ $seatNo }}" class="peer sr-only">
                                                <div class="w-12 h-12 rounded-lg border-2 flex flex-col items-center justify-center transition-all bg-white
                                                    peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white peer-checked:shadow-md
                                                    border-gray-300 text-gray-500 hover:border-blue-400 hover:text-blue-500 hover:shadow-sm">
                                                    <span class="text-sm font-bold">{{ $seatNo }}</span>
                                                </div>
                                                <!-- Tooltip -->
                                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                    Kursi {{ $seatNo }}
                                                </span>
                                            </label>
                                        @endif
                                    @endforeach

                                    <!-- Aisle -->
                                    <div class="flex items-center justify-center text-xs text-gray-300 font-medium w-6">{{ $r }}</div>

                                    <!-- Right Side (C, D) -->
                                    @foreach(['C', 'D'] as $col)
                                        @php 
                                            // Handle potential whitespace issues
                                            $seatNo = trim($r . $col); 
                                            $isOccupied = in_array($seatNo, $occupiedSeats ?? []);
                                        @endphp
                                        
                                        @if($isOccupied)
                                            <div class="w-12 h-12 rounded-lg border-2 bg-gray-100 border-gray-200 flex flex-col items-center justify-center cursor-not-allowed relative">
                                                <span class="text-sm font-bold text-gray-300">{{ $seatNo }}</span>
                                                <div class="absolute inset-0 flex items-center justify-center text-lg font-bold text-gray-400 select-none">X</div>
                                            </div>
                                        @else
                                            <label class="relative group cursor-pointer group">
                                                <input type="radio" name="seat_number" value="{{ $seatNo }}" class="peer sr-only">
                                                <div class="w-12 h-12 rounded-lg border-2 flex flex-col items-center justify-center transition-all bg-white
                                                    peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white peer-checked:shadow-md
                                                    border-gray-300 text-gray-500 hover:border-blue-400 hover:text-blue-500 hover:shadow-sm">
                                                    <span class="text-sm font-bold">{{ $seatNo }}</span>
                                                </div>
                                                <!-- Tooltip -->
                                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                    Kursi {{ $seatNo }}
                                                </span>
                                            </label>
                                        @endif
                                    @endforeach
                                @endfor
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-wrap justify-center gap-6 mt-6 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-white border-2 border-gray-300"></div> 
                                <span>Tersedia</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-gray-200 border-2 border-gray-200 cursor-not-allowed opacity-50 relative overflow-hidden">
                                     <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">X</div>
                                </div> 
                                <span>Terisi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-blue-600 border-2 border-blue-600 shadow-sm"></div> 
                                <span>Dipilih</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proceed Button -->
                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition duration-200 text-lg flex justify-between items-center">
                    <span>Lanjut ke Pembayaran</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </button>
            </form>

            <!-- Right: Trip Summary -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rincian Perjalanan</h3>
                    
                        <div class="flex flex-col items-center mr-3 pt-1">
                            <div class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                            <div class="w-0.5 h-10 bg-gray-200 my-1"></div>
                            <div class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                        </div>
                        <div class="flex-1">
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-0.5">Berangkat</p>
                                <p class="text-sm font-bold text-gray-900">{{ $schedule->route->sourceDestination->city_name ?? $schedule->route->source }}</p>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} • {{ \Carbon\Carbon::parse($travelDate)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Tujuan</p>
                                <p class="text-sm font-bold text-gray-900">{{ $schedule->route->destination->city_name ?? $schedule->route->destination_code }}</p>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }} • {{ \Carbon\Carbon::parse($travelDate)->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 py-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-blue-50 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $schedule->bus->bus_number }}</p>
                                <p class="text-xs text-gray-500">{{ $schedule->bus->bus_type }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Rincian Harga</h4>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Tiket Bus (x1)</span>
                            <span class="text-sm font-medium text-gray-900">Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Biaya Layanan</span>
                            <span class="text-sm font-medium text-green-600">Gratis</span>
                        </div>
                        <div class="border-t border-dashed border-gray-200 pt-3 mt-3 flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Total Pembayaran</span>
                            <span class="text-xl font-bold text-orange-500">Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}</span>
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
    // Other scripts if needed
</script>
@endpush
