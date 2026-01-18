@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ url('/') }}" class="p-2 bg-white rounded-full shadow-sm text-gray-500 hover:text-gray-700 transition border border-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Daftar Pesanan</h1>
        </div>

        @if($bookings->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pesanan</h3>
                <p class="text-gray-500 text-center max-w-sm mb-6">Nikmati kemudahan pemesanan tiket bus antarkota dengan harga terbaik.</p>
                <a href="{{ url('/') }}" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Cari Tiket Sekarang
                </a>
            </div>
        @else
            <!-- Booking List -->
            <div class="space-y-4">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="p-6">
                            <!-- Top Row: ID and Status -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Pemesanan</span>
                                        <p class="text-sm font-mono font-bold text-gray-900">{{ $booking->id }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Jadwal</span>
                                        <p class="text-sm font-mono font-bold text-gray-900">{{ $booking->schedule->id }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    @php
                                        $statusClasses = match($booking->status) {
                                            \App\Models\Booking::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                                            \App\Models\Booking::STATUS_BOOKED => 'bg-green-100 text-green-800', 
                                            \App\Models\Booking::STATUS_CANCELLED => 'bg-red-100 text-red-800',
                                            \App\Models\Booking::STATUS_EXPIRED => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusClasses }}">
                                        {{ $booking->status }}
                                    </span>
                                    @if($booking->schedule->remarks)
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-400 text-blue-900 shadow-sm border border-yellow-500/20 text-center">
                                            {{ $booking->schedule->remarks }}
                                        </span>
                                    @endif
                                    
                                    <!-- Bookmark Button -->
                                    <button onclick="toggleHistoryBookmark(this, '{{ $booking->id }}', '{{ addslashes('App\Models\Booking') }}')" 
                                            class="p-1 px-2 rounded-lg border border-gray-200 hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors {{ $booking->isBookmarkedBy(Auth::user()) ? 'text-red-500' : '' }}" 
                                            title="Simpan ke dalam bookmark">
                                        <svg class="w-5 h-5" fill="{{ $booking->isBookmarkedBy(Auth::user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Middle Row: Trip Info -->
                            <div class="flex flex-col md:flex-row md:items-center gap-6 mb-4">
                                <!-- Bus Info -->
                                <div class="flex items-center gap-4 md:w-1/3">
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ $booking->schedule->bus ? $booking->schedule->bus->bus_name : 'Bus Not Assigned' }}</h4>
                                        <p class="text-xs text-gray-500">{{ $booking->schedule->bus ? ($booking->schedule->bus->bus_number . ' • ' . $booking->schedule->bus->type) : 'Pending Assignment' }}</p>
                                    </div>
                                </div>

                                <!-- Route Info -->
                                <div class="flex-1 border-l border-gray-100 pl-0 md:pl-6">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $booking->schedule->route ? ($booking->schedule->route->sourceDestination->city_name ?? $booking->schedule->route->source) : ($booking->schedule->route_source ?? 'N/A') }}
                                        </span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $booking->schedule->route ? ($booking->schedule->route->destination->city_name ?? $booking->schedule->route->destination_code) : ($booking->schedule->route_destination ?? 'N/A') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('l, d F Y') }} • {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bottom Row: Price and Action -->
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500">Total Harga</p>
                                    <p class="text-lg font-bold text-orange-500">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                                </div>

                                @if($booking->status === \App\Models\Booking::STATUS_PENDING)
                                    <a href="{{ route('booking.payment', $booking->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                        Lanjut Bayar
                                    </a>
                                @elseif($booking->status === \App\Models\Booking::STATUS_BOOKED)
                                    <a href="{{ route('booking.ticket', $booking->id) }}" class="px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 text-sm font-medium rounded-lg transition-colors">
                                        Lihat E-Tiket
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function toggleHistoryBookmark(button, id, type) {
        const svg = button.querySelector('svg');
        const isBookmarked = button.classList.contains('text-red-500');
        
        // Optimistic UI
        button.classList.toggle('text-red-500');
        svg.setAttribute('fill', isBookmarked ? 'none' : 'currentColor');

        try {
            const res = await fetch('{{ route("bookmarks.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    bookmarkable_id: id,
                    bookmarkable_type: type
                })
            });

            if (!res.ok) throw new Error('Failed to toggle bookmark');
            
        } catch (e) {
            console.error(e);
            // Revert on failure
            button.classList.toggle('text-red-500');
            svg.setAttribute('fill', isBookmarked ? 'currentColor' : 'none');
            alert('Gagal mengubah bookmark.');
        }
    }
</script>
@endpush
