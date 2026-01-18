@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Edit Pemesanan #{{ $booking->id }}</h2>
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

                <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Info Panel -->
                        <div class="bg-gray-50 p-4 rounded-md border text-sm">
                            <h3 class="font-bold text-gray-700 mb-2">Informasi Perjalanan</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-gray-500">Rute:</span>
                                <span class="font-medium">{{ $booking->schedule->route->source }} -> {{ $booking->schedule->route->destination->city_name ?? $booking->schedule->route->destination_code }}</span>
                                
                                <span class="text-gray-500">Tanggal:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('d F Y, H:i') }}</span>
                                
                                <span class="text-gray-500">Bus:</span>
                                <span class="font-medium">{{ $booking->schedule->bus->bus_name }} ({{ $booking->schedule->bus->bus_number }})</span>
                                
                                <span class="text-gray-500">Pelanggan:</span>
                                <span class="font-medium text-blue-600">
                                    @if($booking->account_id)
                                        <a href="{{ route('admin.users.edit', $booking->account_id) }}" class="hover:underline">
                                            {{ $booking->account->first_name ?? 'Unknown' }} {{ $booking->account->last_name ?? '' }}
                                        </a>
                                    @else
                                        {{ $booking->customer_name ?? 'Guest' }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Status Panel -->
                        <div class="bg-gray-50 p-4 rounded-md border text-sm">
                            <h3 class="font-bold text-gray-700 mb-2">Status Pemesanan</h3>
                            <div class="mb-4">
                                <label class="block text-gray-500 mb-1">Status Utama</label>
                                <div x-data="{ 
                                    open: false, 
                                    selected: '{{ $booking->status }}', 
                                    label: '',
                                    options: {
                                        'Booked': 'Booked (Dipesan)',
                                        'Pending': 'Pending (Menunggu Pembayaran)',
                                        'Cancelled': 'Cancelled (Dibatalkan)',
                                        'Expired': 'Expired (Kedaluwarsa)'
                                    },
                                    init() { this.label = this.options[this.selected] || this.selected; }
                                }" class="relative">
                                    <input type="hidden" name="status" x-model="selected">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm h-[42px]">
                                        <span class="block truncate" x-text="label"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md ring-1 ring-black ring-opacity-5 overflow-hidden sm:text-sm">
                                        <div class="max-h-60 overflow-y-auto">
                                            <template x-for="(text, value) in options" :key="value">
                                                <div @click="selected = value; label = text; open = false" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">
                                                    <span x-text="text" class="font-normal block truncate"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-500">Status Pembayaran:</span>
                                <span class="ml-2 font-bold {{ ($booking->payment->status ?? '') == 'Success' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $booking->payment->status ?? 'Payment Not Found' }}
                                </span>
                            </div>
                            <div class="mt-2">
                                <span class="text-gray-500">Total:</span>
                                <span class="ml-2 font-bold text-lg">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger & Seat Editing -->
                    <div class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Penumpang & Kursi</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Tiket</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kursi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Penumpang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Tiket</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($booking->tickets as $ticket)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            {{ $ticket->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $ticket->seat_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="text" name="passengers[{{ $ticket->id }}]" value="{{ $ticket->passenger_name }}" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 w-full p-2">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $ticket->status }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-6 border-t">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-medium transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
