@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12" x-data="bookingApp({{ $schedule->id }}, '{{ $date }}', {{ $schedule->price_per_seat }}, {{ $schedule->bus->capacity ?? 40 }})">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Seat Selection -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Pilih Kursi</h2>
                    
                    <div class="flex justify-center mb-8 gap-6">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-gray-200 border border-gray-300"></div>
                            <span class="text-sm text-gray-600">Tersedia</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-blue-600 border border-blue-700"></div>
                            <span class="text-sm text-gray-600">Dipilih</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-red-500 opacity-50 cursor-not-allowed"></div>
                            <span class="text-sm text-gray-600">Terisi</span>
                        </div>
                    </div>

                    <!-- Driver Area -->
                    <div class="flex justify-end mb-10 px-10">
                        <div class="w-12 h-12 rounded-full border-2 border-gray-300 flex items-center justify-center bg-gray-50">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        </div>
                    </div>

                    <!-- Seat Grid -->
                    <div class="max-w-md mx-auto relative">
                        <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 z-10 flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 gap-y-6 justify-items-center">
                            <template x-for="seat in seats" :key="seat">
                                <button 
                                    @click="toggleSeat(seat)"
                                    :disabled="takenSeats.includes(seat)"
                                    class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold transition-all duration-200 relative group"
                                    :class="{
                                        'bg-gray-200 text-gray-700 hover:bg-gray-300 border border-gray-300': !takenSeats.includes(seat) && !selectedSeats.includes(seat),
                                        'bg-blue-600 text-white shadow-md transform scale-105 border border-blue-700': selectedSeats.includes(seat),
                                        'bg-red-500 text-white opacity-50 cursor-not-allowed': takenSeats.includes(seat)
                                    }"
                                >
                                    <span x-text="seat"></span>
                                    <!-- Tooltip for taken -->
                                    <div x-show="takenSeats.includes(seat)" class="hidden group-hover:block absolute bottom-full mb-2 w-max px-2 py-1 bg-gray-800 text-white text-xs rounded">Terisi</div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pemesanan</h3>
                    
                    <div class="space-y-4 mb-6">
                        <div class="pb-4 border-b border-gray-100">
                            <div class="text-sm text-gray-500">Rute</div>
                            <div class="font-semibold text-gray-800">{{ $schedule->route->sourceDestination->city_name }} &rarr; {{ $schedule->route->destination->city_name }}</div>
                        </div>
                        <div class="pb-4 border-b border-gray-100">
                            <div class="text-sm text-gray-500">Jadwal</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}<br>
                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}
                            </div>
                        </div>
                        <div class="pb-4 border-b border-gray-100">
                            <div class="text-sm text-gray-500">Bus</div>
                            <div class="font-semibold text-gray-800">{{ $schedule->bus->bus_number }} ({{ $schedule->bus->bus_type }})</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Kursi Dipilih</span>
                            <span class="font-medium text-gray-900" x-text="selectedSeats.join(', ') || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Harga Satuan</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-200 my-2 pt-2 flex justify-between items-center">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="font-bold text-blue-600 text-lg" x-text="'Rp ' + (selectedSeats.length * price).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <button 
                        @click="submitBooking"
                        :disabled="selectedSeats.length === 0 || submitting"
                        class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        <span x-show="!submitting">Bayar Sekarang</span>
                        <span x-show="submitting">Memproses...</span>
                    </button>
                    <p class="text-xs text-gray-500 mt-3 text-center">Pastikan detail perjalanan Anda sudah benar sebelum melanjutkan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function bookingApp(scheduleId, travelDate, pricePerSeat, totalSeats) {
        return {
            scheduleId: scheduleId,
            date: travelDate,
            price: pricePerSeat,
            takenSeats: [],
            selectedSeats: [],
            loading: true,
            submitting: false,
            seats: [],

            async init() {
                this.generateSeats(totalSeats);
                await this.fetchTakenSeats();
            },

            generateSeats(total) {
                // Determine seat config based on Bus Type if possible (currently generic 1..N)
                // Assuming numeric seats 1 to N
                this.seats = Array.from({length: total}, (_, i) => String(i + 1));
            },

            async fetchTakenSeats() {
                try {
                    const res = await fetch(`/api/schedules/${this.scheduleId}/seats?date=${this.date}`);
                    const data = await res.json();
                    this.takenSeats = data.taken_seats?.map(String) || [];
                } catch (e) {
                    console.error('Failed to fetch seats', e);
                } finally {
                    this.loading = false;
                }
            },

            toggleSeat(seat) {
                if (this.takenSeats.includes(seat)) return;
                
                if (this.selectedSeats.includes(seat)) {
                    this.selectedSeats = this.selectedSeats.filter(s => s !== seat);
                } else {
                    if (this.selectedSeats.length >= 5) {
                        alert('Maksimal 5 kursi per pemesanan.');
                        return;
                    }
                    this.selectedSeats.push(seat);
                }
            },

            async submitBooking() {
                if (this.selectedSeats.length === 0) return;
                
                this.submitting = true;
                
                try {
                    const res = await fetch('/api/bookings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            schedule_id: this.scheduleId,
                            travel_date: this.date,
                            seats: this.selectedSeats
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (!res.ok) {
                        throw new Error(data.message || 'Booking Failed');
                    }
                    
                    // Success
                    // Redirect to My Bookings or Success Page
                    window.location.href = '/my-bookings'; // Or a success page
                    
                } catch (e) {
                    alert(e.message);
                    this.fetchTakenSeats(); // Refresh seats in case of conflict
                } finally {
                    this.submitting = false;
                }
            }
        }
    }
</script>
@endsection
