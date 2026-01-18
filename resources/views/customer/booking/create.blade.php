@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Ticket Booking</h1>
                <a href="{{ route('schedules.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Change Schedule / Search Again
                </a>
            </div>
            <div class="flex items-center justify-center">
                <div class="flex items-start w-full max-w-3xl">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">1</div>
                        <span class="text-sm font-medium text-blue-600 mt-2">Fill Details</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-300 mx-4 mt-3.5"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">2</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Payment</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-300 mx-4 mt-3.5"></div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">3</div>
                        <span class="text-sm font-medium text-gray-500 mt-2">Finish</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="font-medium text-red-600">Whoops! There were some problems with your input.</div>
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
                            <h4 class="text-sm font-semibold text-blue-900">Login for easier booking</h4>
                            <p class="text-sm text-blue-700 mt-1">Save passenger details and enjoy special member promos.</p>
                            <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 mt-2 inline-block">Login / Sign Up &rarr;</a>
                        </div>
                    </div>
                @endguest

                <!-- Contact Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Booker Details
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}">
                                <span class="text-xs text-gray-500 mt-1">According to ID Card/Passport</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" placeholder="Ex: 08123456789">
                                <span class="text-xs text-gray-500 mt-1">E-ticket will be sent via WhatsApp</span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" value="{{ auth()->user()->email ?? '' }}">
                                <span class="text-xs text-gray-500 mt-1">E-ticket will be sent to this email</span>
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
                        Select Seats
                    </h3>
                    
                    <div class="flex flex-col items-center">
                        <!-- Driver Position -->
                        <div class="w-full max-w-xs flex justify-end mb-6 pr-2">
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-8 h-8 rounded-full border-2 border-gray-400 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">Driver</span>
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
                                                <input type="checkbox" name="seats[]" value="{{ $seatNo }}" class="peer sr-only seat-checkbox">
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
                                                <input type="checkbox" name="seats[]" value="{{ $seatNo }}" class="peer sr-only seat-checkbox">
                                                <div class="w-12 h-12 rounded-lg border-2 flex flex-col items-center justify-center transition-all bg-white
                                                    peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white peer-checked:shadow-md
                                                    border-gray-300 text-gray-500 hover:border-blue-400 hover:text-blue-500 hover:shadow-sm">
                                                    <span class="text-sm font-bold">{{ $seatNo }}</span>
                                                </div>
                                                <!-- Tooltip -->
                                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                    Seat {{ $seatNo }}
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
                                <span>Available</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-gray-200 border-2 border-gray-200 cursor-not-allowed opacity-50 relative overflow-hidden">
                                     <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">X</div>
                                </div> 
                                <span>Occupied</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-blue-600 border-2 border-blue-600 shadow-sm"></div> 
                                <span>Selected</span>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Details Section -->
                    <div id="passengerSection" class="mb-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Passenger Details
                        </h3>
                        <div id="passengerRows" class="space-y-4"></div>
                    </div>

                    <!-- Split Bill Section -->
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-900">Split Payment (Split Bill)</h4>
                                <p class="text-sm text-gray-500">Pay tickets separately for each passenger</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="splitBillToggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div id="splitBillContainer" class="hidden space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                             <!-- Dynamic Rows -->
                             <p class="text-sm text-gray-500 italic" id="emptySplitMsg">Select seats first to configure payment.</p>
                             <div id="splitRows" class="space-y-3"></div>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
                        const splitBillToggle = document.getElementById('splitBillToggle');
                        const splitBillContainer = document.getElementById('splitBillContainer');
                        const splitRows = document.getElementById('splitRows');
                        const emptySplitMsg = document.getElementById('emptySplitMsg');
                        
                        const passengerSection = document.getElementById('passengerSection');
                        const passengerRows = document.getElementById('passengerRows');

                        const totalEstimateDisplay = document.getElementById('totalEstimateDisplay');
                        const pricePerSeat = {{ $schedule->price_per_seat }};

                        // Data Pemesan for default value
                        const defaultName = "{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}".trim();

                        function updateRows() {
                            const selectedSeats = Array.from(seatCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
                            
                            // Update Estimate
                            const total = selectedSeats.length * pricePerSeat;
                            if(totalEstimateDisplay) {
                                totalEstimateDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                            }

                            // ---- Passenger Rows ----
                            const currentPassengerNames = {};
                            document.querySelectorAll('.passenger-input').forEach(input => {
                                currentPassengerNames[input.dataset.seat] = input.value;
                            });

                            if(passengerRows) {
                                passengerRows.innerHTML = '';
                                if (selectedSeats.length > 0) {
                                    passengerSection.classList.remove('hidden');
                                    selectedSeats.forEach((seat, index) => {
                                        // Default logic: First seat gets Booker Name if empty, others empty
                                        let val = currentPassengerNames[seat] || '';
                                        if (!val && index === 0 && !currentPassengerNames[seat]) {
                                            val = defaultName;
                                        }

                                        const pRow = document.createElement('div');
                                        pRow.className = 'bg-gray-50 p-4 rounded-xl border border-gray-200';
                                        pRow.innerHTML = `
                                            <div class="flex flex-col md:flex-row gap-4 items-start md:items-center">
                                                <div class="flex items-center gap-3 w-32 shrink-0">
                                                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                                        ${seat}
                                                    </div>
                                                    <span class="text-sm font-bold text-gray-700">Seat ${seat}</span>
                                                </div>
                                                <div class="flex-1 w-full">
                                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Passenger Name</label>
                                                    <input type="text" name="passengers[${seat}]" data-seat="${seat}" class="passenger-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" placeholder="Full Name" value="${val}" required>
                                                </div>
                                            </div>
                                        `;
                                        passengerRows.appendChild(pRow);
                                    });
                                } else {
                                    passengerSection.classList.add('hidden');
                                }
                            }


                            // ---- Split Bill Rows ----
                            // Capture current split values
                            const currentSplitValues = {};
                            document.querySelectorAll('.split-bill-input').forEach(input => {
                                currentSplitValues[input.dataset.seat] = input.value;
                            });

                            if(splitRows) {
                                splitRows.innerHTML = '';

                                if (selectedSeats.length === 0) {
                                    emptySplitMsg.classList.remove('hidden');
                                } else {
                                    emptySplitMsg.classList.add('hidden');
                                    selectedSeats.forEach(seat => {
                                        const val = currentSplitValues[seat] || 'main';
                                        
                                        let optionsHtml = '';
                                        let selectedText = 'My Bill (Main)'; // Default

                                        // Main Option
                                        optionsHtml += `<div class="custom-dropdown-item px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" data-value="main">My Bill (Main)</div>`;
                                        if(val === 'main') selectedText = 'My Bill (Main)';

                                        // Dynamic Options
                                        for (let i = 1; i <= selectedSeats.length; i++) {
                                            const billId = `bill_${i}`;
                                            const label = `New Bill #${i}`;
                                            optionsHtml += `<div class="custom-dropdown-item px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" data-value="${billId}">${label}</div>`;
                                            if(val === billId) selectedText = label;
                                        }

                                        const row = document.createElement('div');
                                        row.className = 'flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 shadow-sm';
                                        row.innerHTML = `
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                                    ${seat}
                                                </div>
                                                <span class="text-sm font-medium text-gray-700">Seat ${seat}</span>
                                            </div>
                                            <div class="relative custom-dropdown w-1/2">
                                                <input type="hidden" name="split_bill[${seat}]" data-seat="${seat}" value="${val}" class="split-bill-input p-2">
                                                <button type="button" class="custom-dropdown-btn w-full bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg flex items-center justify-between shadow-sm text-sm p-2 transition-colors hover:bg-gray-50">
                                                    <span class="dropdown-text truncate">${selectedText}</span>
                                                    <svg class="w-4 h-4 ml-2 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </button>
                                                <div class="custom-dropdown-menu hidden absolute right-0 mt-2 w-full bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 overflow-y-auto max-h-60">
                                                    ${optionsHtml}
                                                </div>
                                            </div>
                                        `;
                                        splitRows.appendChild(row);
                                    });
                                }
                            }
                        }

                        // --- Dropdown Event Listeners ---
                        document.addEventListener('click', function(e) {
                            const isBtn = e.target.closest('.custom-dropdown-btn');
                            const isItem = e.target.closest('.custom-dropdown-item');
                            const isMenu = e.target.closest('.custom-dropdown-menu');

                            // Close all if clicking outside (not on btn, not on menu, not on item)
                            // But if clicking Item, we handle it separately.
                            if (!isBtn && !isMenu && !isItem) {
                                document.querySelectorAll('.custom-dropdown-menu').forEach(m => m.classList.add('hidden'));
                            }

                            // Toggle
                            if (isBtn) {
                                const menu = isBtn.nextElementSibling; // div.custom-dropdown-menu
                                // Close others
                                document.querySelectorAll('.custom-dropdown-menu').forEach(m => {
                                    if (m !== menu) m.classList.add('hidden');
                                });
                                menu.classList.toggle('hidden');
                            }

                            // Select
                            if (isItem) {
                                const container = isItem.closest('.custom-dropdown');
                                const hiddenInput = container.querySelector('.split-bill-input');
                                const btnText = container.querySelector('.dropdown-text');
                                
                                hiddenInput.value = isItem.dataset.value;
                                btnText.textContent = isItem.textContent.trim();
                                
                                // Close menu
                                isItem.closest('.custom-dropdown-menu').classList.add('hidden');
                            }
                        });


                        if(splitBillToggle) {
                            splitBillToggle.addEventListener('change', function() {
                                if (this.checked) {
                                    splitBillContainer.classList.remove('hidden');
                                    updateRows(); 
                                    // Enable? Inputs are text/hidden, explicit disable not strictly needed for hidden but good for hygiene
                                    document.querySelectorAll('.split-bill-input').forEach(el => el.disabled = false);
                                } else {
                                    splitBillContainer.classList.add('hidden');
                                    document.querySelectorAll('.split-bill-input').forEach(el => el.disabled = true);
                                }
                            });
                        }


                        seatCheckboxes.forEach(cb => {
                            cb.addEventListener('change', updateRows);
                        });
                    });
                </script>
                @endpush

                <!-- Proceed Button -->
                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition duration-200 text-lg flex justify-between items-center">
                    <span>Proceed to Payment</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </button>
            </form>

            <!-- Right: Trip Summary -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Trip Details</h3>
                    </div>
                    
                    <div class="p-6">
                        <!-- Route Timeline -->
                        <div class="relative pl-4 border-l-2 border-gray-200 ml-2 space-y-8 mb-6">
                            <!-- Departure -->
                            <div class="relative">
                                <div class="absolute -left-[21px] top-1.5 w-3.5 h-3.5 bg-white border-4 border-blue-500 rounded-full"></div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $schedule->route->sourceDestination->city_name ?? $schedule->route->source }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y, H:i') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Terminal {{ $schedule->route->source_code }}</p>
                                </div>
                            </div>

                            <!-- Arrival -->
                            <div class="relative">
                                <div class="absolute -left-[21px] top-1.5 w-3.5 h-3.5 bg-white border-4 border-indigo-500 rounded-full"></div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $schedule->route->destination->city_name ?? $schedule->route->destination_code }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('d M Y, H:i') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Terminal {{ $schedule->route->destination_code }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Travel Duration -->
                         <div class="bg-blue-50 rounded-lg p-3 mb-6 flex items-center justify-between">
                            <span class="text-xs font-semibold text-blue-800">Travel Duration</span>
                             <span class="text-xs font-bold text-blue-900">
                                {{ \Carbon\Carbon::parse($schedule->departure_time)->diff(\Carbon\Carbon::parse($schedule->arrival_time))->format('%h Hours %i Minutes') }}
                            </span>
                        </div>


                        <!-- Bus Info -->
                        <div class="border-t border-gray-100 pt-4 mb-2">
                             <div class="flex items-center gap-3">
                                <div class="bg-blue-50 p-2 rounded-lg shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $schedule->bus->bus_name ?? 'Bus' }}</p>
                                    <p class="text-xs text-gray-500">{{ $schedule->bus->bus_number ?? '-' }} • {{ $schedule->bus->bus_type ?? 'Standard' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Price per Ticket</span>
                                <span class="text-sm font-bold text-gray-900">Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}</span>
                            </div>
                           
                            <div class="border-t border-dashed border-gray-200 pt-3 mt-3 flex justify-between items-center">
                                <span class="text-base font-bold text-gray-900">Total Estimate</span>
                                <span class="text-xl font-bold text-blue-600" id="totalEstimateDisplay">Rp 0</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 italic text-right">*Total based on selected seats</p>
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
