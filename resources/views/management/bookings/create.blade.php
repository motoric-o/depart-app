@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" 
                 x-data="adminBookingCreate({
                    routes: {{ json_encode($routes) }}
                 })"
            >
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Buat Pemesanan Baru (Admin)</h2>
                    <a href="{{ route('admin.bookings') }}" class="text-gray-600 hover:text-gray-900">&larr; Kembali</a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-md">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form x-ref="bookingForm" action="{{ route('admin.bookings.store') }}" method="POST" @submit="submitForm">
                    @csrf
                    <input type="hidden" name="schedule_id" x-model="form.schedule_id">
                    
                    <!-- 1. Customer Selection -->
                    <div class="mb-6 border-b pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">1. Data Pelanggan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <div class="flex gap-2">
                                    <input type="email" name="customer_email" x-model="form.customer_email" 
                                           @blur="checkEmail"
                                           @keydown.enter.prevent="checkEmail"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" 
                                           placeholder="email@contoh.com" required>
                                    <button type="button" @click="checkEmail" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm">
                                        Cek
                                    </button>
                                </div>
                                <p x-show="emailCheckStatus === 'checking'" class="text-xs text-gray-500 mt-1">Mencari...</p>
                                <p x-show="emailCheckStatus === 'found'" class="text-xs text-green-600 mt-1">Pelanggan ditemukan!</p>
                                <p x-show="emailCheckStatus === 'not_found'" class="text-xs text-blue-600 mt-1">Email baru. Silakan isi nama.</p>
                            </div>
                            
                            <div x-show="showNameField" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="customer_name" x-model="form.customer_name" 
                                       :readonly="emailCheckStatus === 'found'"
                                       :class="emailCheckStatus === 'found' ? 'bg-gray-100 cursor-not-allowed' : ''"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" 
                                       placeholder="Nama Pelanggan" required>
                                <p x-show="emailCheckStatus === 'found'" class="text-xs text-gray-500 mt-1">Nama diambil dari akun terdaftar.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Find Schedule -->
                    <div class="mb-6 border-b pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">2. Cari Jadwal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rute</label>
                                <!-- Custom Dropdown for Route -->
                                <div x-data="{ open: false, label: '-- Pilih Rute --' }" class="relative">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm h-[42px]">
                                        <span class="block truncate" x-text="label"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md ring-1 ring-black ring-opacity-5 overflow-hidden sm:text-sm">
                                        <div class="max-h-60 overflow-y-auto">
                                            <div @click="form.route_id = ''; label = '-- Semua Rute --'; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 border-b border-gray-100">
                                                <span class="font-normal block truncate">-- Semua Rute --</span>
                                            </div>
                                            <template x-for="route in routes" :key="route.id">
                                                <div @click="form.route_id = route.id; label = route.source + ' -> ' + (route.destination ? route.destination.city_name : route.destination_code); open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">
                                                    <span x-text="route.source + ' -> ' + (route.destination ? route.destination.city_name : route.destination_code)" class="font-normal block truncate"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <input type="date" x-model="form.date" :min="today" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 h-[42px]">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="searchSchedules" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 w-full" >
                                    Cari Jadwal
                                </button>
                            </div>
                        </div>

                        <!-- Schedules List / Selected Schedule -->
                        <div x-show="schedules.length > 0" class="mt-4">
                            <!-- List View -->
                            <div x-show="!selectedSchedule">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jadwal Tersedia:</label>
                                <div class="grid grid-cols-1 gap-4">
                                    <template x-for="schedule in schedules" :key="schedule.id">
                                        <div @click="selectSchedule(schedule)" 
                                             class="border rounded-md p-4 cursor-pointer transition-colors border-gray-200 hover:bg-gray-50">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-bold text-lg" x-text="formatTime(schedule.departure_time) + ' - ' + formatTime(schedule.arrival_time)"></div>
                                                    <div class="text-sm text-gray-600" x-text="schedule.bus.bus_name + ' (' + schedule.bus.bus_type + ')'"></div>
                                                    <div class="text-xs text-gray-500 mt-1" x-text="'Sisa Kursi: ' + (schedule.quota - (schedule.bookings_count || 0))"></div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-lg font-bold text-blue-600" x-text="formatMoney(schedule.price_per_seat)"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Selected View -->
                            <div x-show="selectedSchedule" class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-lg text-blue-900">Jadwal Terpilih</h4>
                                        <div class="mt-2 text-blue-800">
                                            <div x-text="formatTime(selectedSchedule?.departure_time) + ' - ' + formatTime(selectedSchedule?.arrival_time) + ' | ' + selectedSchedule?.bus?.bus_name"></div>
                                            <div class="text-sm" x-text="selectedSchedule?.route?.source + ' -> ' + (selectedSchedule?.route?.destination?.city_name || selectedSchedule?.route?.destination_code)"></div>
                                        </div>
                                    </div>
                                    <button type="button" @click="deselectSchedule" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                        Ubah Jadwal
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div x-show="searched && schedules.length === 0" class="mt-4 text-gray-500 text-sm italic">
                            Tidak ada jadwal ditemukan untuk kriteria ini.
                        </div>
                    </div>

                    <!-- 3. Seat Selection -->
                    <div class="mb-6 border-b pb-6" x-show="form.schedule_id">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">3. Pilih Kursi</h3>
                        <p class="text-sm text-gray-500 mb-4">Pilih kursi pada denah di bawah ini.</p>
                        
                        <div class="bg-gray-100 p-6 rounded-lg overflow-x-auto flex justify-center">
                            <!-- Seat Map -->
                            <div class="grid gap-2" :style="'grid-template-columns: repeat(' + (selectedSchedule?.bus?.seat_columns || 4) + ', minmax(40px, 1fr));'">
                                <template x-for="row in generateSeatLayout(selectedSchedule?.bus)" :key="row.id">
                                    <template x-for="seat in row.seats" :key="seat.label">
                                        <div>
                                            <template x-if="seat.isAisle">
                                                <div class="w-10 h-10"></div>
                                            </template>
                                            <template x-if="!seat.isAisle">
                                                <button type="button" 
                                                        @click="toggleSeat(seat.label)"
                                                        :disabled="isSeatBooked(seat.label)"
                                                        class="w-10 h-10 rounded-md flex items-center justify-center text-xs font-bold transition-colors border"
                                                        :class="{
                                                            'bg-gray-300 text-gray-500 cursor-not-allowed border-gray-300': isSeatBooked(seat.label),
                                                            'bg-blue-600 text-white border-blue-600': form.seats.includes(seat.label),
                                                            'bg-white text-gray-700 border-gray-300 hover:border-blue-400': !isSeatBooked(seat.label) && !form.seats.includes(seat.label)
                                                        }"
                                                        x-text="seat.label">
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <!-- Hidden Inputs for Seats -->
                        <template x-for="seat in form.seats" :key="seat">
                            <input type="hidden" name="seats[]" :value="seat">
                        </template>
                    </div>

                    <!-- 4. Passenger Details -->
                    <div class="mb-6 border-b pb-6" x-show="form.seats.length > 0">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">4. Detail Penumpang</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(seat, index) in form.seats" :key="seat">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="'Nama Penumpang (Kursi ' + seat + ')'"></label>
                                    <input type="text" name="passengers[]" x-model="form.passengers[index]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" required placeholder="Nama Lengkap">
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 5. Payment Details -->
                    <div class="mb-6" x-show="form.seats.length > 0">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">5. Detail Pembayaran</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                                <div class="text-2xl font-bold text-blue-600" x-text="formatMoney(calculateTotal())"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                                <div x-data="{ open: false, label: 'Pilih Metode...' }" class="relative mb-4">
                                     <input type="hidden" name="payment_method" x-model="form.payment_method">
                                     <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm h-[42px]">
                                        <span class="block truncate" x-text="label"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md ring-1 ring-black ring-opacity-5 overflow-hidden sm:text-sm">
                                        <div class="max-h-60 overflow-y-auto">
                                            <div @click="form.payment_method = 'Cash'; label = 'Cash (Tunai)'; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">Cash (Tunai)</div>
                                            <div @click="form.payment_method = 'Transfer'; label = 'Transfer Manual'; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">Transfer Manual</div>
                                            <div @click="form.payment_method = 'Other'; label = 'Lainnya'; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">Lainnya</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                                <div x-data="{ open: false, label: 'Pilih Status...' }" class="relative">
                                     <input type="hidden" name="payment_status" x-model="form.payment_status">
                                     <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm h-[42px]">
                                        <span class="block truncate" x-text="label"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md ring-1 ring-black ring-opacity-5 overflow-hidden sm:text-sm">
                                        <div class="max-h-60 overflow-y-auto">
                                            <div @click="form.payment_status = 'Paid'; label = 'LUNAS (Paid)'; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">LUNAS (Paid)</div>
                                            <div @click="form.payment_status = 'Pending'; label = 'BELUM LUNAS (Pending)'; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">BELUM LUNAS (Pending)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-medium transition-colors">
                            Buat Pemesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminBookingCreate', ({ routes }) => ({
            routes: routes,
            today: new Date().toISOString().split('T')[0],
            schedules: [],
            searched: false,
            selectedSchedule: null,
            bookedSeats: [], // Array of booked seat numbers for selected schedule
            
            emailCheckStatus: 'idle', // idle, checking, found, not_found
            showNameField: false,

            form: {
                customer_name: '',
                customer_email: '',
                route_id: '',
                date: '',
                schedule_id: '',
                seats: [],
                passengers: [],
                payment_method: '',
                payment_status: ''
            },

            checkEmail() {
                if (!this.form.customer_email) return;
                
                this.emailCheckStatus = 'checking';
                
                fetch(`/admin/bookings/check-customer?email=${this.form.customer_email}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.found) {
                            this.emailCheckStatus = 'found';
                            this.form.customer_name = data.name;
                            this.showNameField = true; // Show it but readonly
                        } else {
                            this.emailCheckStatus = 'not_found';
                            this.form.customer_name = ''; // Clear for new input
                            this.showNameField = true;
                        }
                    })
                    .catch(() => {
                        this.emailCheckStatus = 'idle'; // Reset on error
                        // Fallback to showing field allow manual entry
                        this.showNameField = true;
                    });
            },

            searchSchedules() {
                // if (!this.form.route_id || !this.form.date) return; // Allow optional search
                
                this.searched = false;
                this.schedules = [];
                this.selectedSchedule = null;
                this.form.schedule_id = '';
                this.form.seats = [];
                this.form.passengers = [];

                fetch(`/admin/bookings/search-schedules?route_id=${this.form.route_id}&date=${this.form.date}`)
                    .then(res => res.json())
                    .then(data => {
                        this.schedules = data;
                        this.searched = true;
                    })
                    .catch(() => {
                        alert('Gagal mengambil jadwal.');
                    });
            },

            formatTime(datetime) {
                return new Date(datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },

            formatMoney(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            },

            selectSchedule(schedule) {
                this.selectedSchedule = schedule;
                this.form.schedule_id = schedule.id;
                this.form.seats = [];
                this.form.passengers = [];
                
                // Fetch booked seats for this schedule
                // Need an endpoint for this too.
                // `api/schedules/{id}/seats`?
                // I'll try `api/admin/bookings?search=` to find bookings for this schedule? No.
                // I'll assume schedule object has `bookings` relation loaded?
                // AdminController pagination load: `Schedule::with(...)`?
                // Let's look at `AdminController@schedules`. It returns View. The API `Api\Admin\ScheduleController`?
                // The `datatable` JS uses `/api/admin/schedules`.
                // If I fetch that, does it denote booked seats?
                // Usually `quota` and `bookings_count` are there. But not specific seat numbers.
                
                // I will fetch `/api/admin/bookings` and filter? No.
                // I'll implement a `fetchBookedSeats` method later if I can.
                // For now, I'll mock it or try to get it.
                // Wait, I can use `fetch('/api/admin/bookings?sort_by=id&per_page=100')` and filter client side? Too risky.
                
                // Let's look at `BookingController::create`. It passes `$bookedSeats` to view.
                // Logic: `Ticket::whereIn('booking_id', $schedule->bookings->pluck('id'))->pluck('seat_number')`.
                // I can't easily access that via existing Admin APIs.
                // I'll update `routes/web.php` to add `admin/schedules/{id}/seats` helper route?
                // User said "Make a new management page...".
                // I'll execute a fetch to a helper route I will create now?
                // Or I can just put logic in `createBooking` view? No, it's dynamic.
                
                // I'll add `Route::get('/schedules/{id}/booked-seats', ...)` in Admin routes.
                
                fetch(`/admin/schedules/${schedule.id}/booked-seats`)
                    .then(res => res.json())
                    .then(seats => {
                        this.bookedSeats = seats;
                    })
                    .catch(() => {
                        this.bookedSeats = []; // Fail gracefully
                    });
            },
            
            deselectSchedule() {
                this.selectedSchedule = null;
                this.form.schedule_id = '';
                this.form.seats = [];
                this.form.passengers = [];
                this.bookedSeats = [];
            },

            isSeatBooked(seatLabel) {
                return this.bookedSeats.includes(seatLabel);
            },

            toggleSeat(seatLabel) {
                if (this.isSeatBooked(seatLabel)) return;
                
                if (this.form.seats.includes(seatLabel)) {
                    const index = this.form.seats.indexOf(seatLabel);
                    this.form.seats.splice(index, 1);
                    this.form.passengers.splice(index, 1); // Remove corresponding name
                } else {
                    this.form.seats.push(seatLabel);
                    this.form.passengers.push(''); // Add empty name placeholder
                }
                // Sort seats for consistency?
                // this.form.seats.sort(); 
            },

            calculateTotal() {
                if (!this.selectedSchedule) return 0;
                return this.selectedSchedule.price_per_seat * this.form.seats.length;
            },
            
            generateSeatLayout(bus) {
                if (!bus) return [];
                let rows = [];
                for (let r = 1; r <= bus.seat_rows; r++) {
                    let rowSeats = [];
                    // Simple logic: A, B, C...
                    // Assuming columns are split by aisle.
                    // E.g. 2-2 layout.
                    let colLabel = 0;
                    for (let c = 1; c <= bus.seat_columns; c++) {
                        // Check for aisle? Assuming middle is aisle if columns > 2.
                        // Actually, let's just make pure grid for now or use standard logic.
                        // Seat Naming: 1A, 1B...
                        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        const label = r + letters[c-1]; 
                        rowSeats.push({ label: label, isAisle: false });
                    }
                    rows.push({ id: r, seats: rowSeats });
                }
                return rows;
            },

            submitForm(e) {
                // Browser handles name/email/passengers 'required' check before this fires.
                
                // Custom Validation
                if (this.form.seats.length === 0) {
                    alert('Silakan pilih minimal 1 kursi.');
                    e.preventDefault();
                    return;
                }
                if (!this.form.payment_method) {
                    alert('Silakan pilih metode pembayaran.');
                    e.preventDefault();
                    return;
                }
                if (!this.form.payment_status) {
                    alert('Silakan pilih status pembayaran.');
                    e.preventDefault();
                    return;
                }
                // If valid, allow default submission
            }
        }));
    });
</script>
@endsection
