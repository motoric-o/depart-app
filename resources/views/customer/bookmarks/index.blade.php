@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
           <div class="flex items-center space-x-4">
            <a href="{{ url('/') }}" class="p-2 bg-white rounded-full shadow-sm text-gray-500 hover:text-gray-700 transition border border-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Bookmarks</h1>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('bookmarks.index') }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !request('filter') ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    All
                </a>
                <a href="{{ route('bookmarks.index', ['filter' => 'schedule']) }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('filter') === 'schedule' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    Schedules
                </a>
                <a href="{{ route('bookmarks.index', ['filter' => 'booking']) }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('filter') === 'booking' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    Bookings
                </a>
            </div>
        </div>

        @if($bookmarks->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No items saved</h3>
                <p class="text-gray-500 max-w-md mx-auto">Bookmark your schedules or booking history for quick access here.</p>
                <a href="{{ route('schedules.index') }}" class="inline-block mt-6 text-blue-600 hover:text-blue-800 font-medium">Search Trip Schedules</a>
            </div>
        @else
            <div class="grid gap-6">
                @foreach($bookmarks as $bookmark)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border border-gray-200 p-6 flex justify-between items-center">
                        <div>
                            @if($bookmark->bookmarkable_type === 'App\Models\Schedule')
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800">Schedule</span>
                                    <span class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($bookmark->bookmarkable->departure_time)->translatedFormat('d F Y, H:i') }}
                                    </span>
                                </div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ $bookmark->bookmarkable->route->sourceDestination->city_name ?? $bookmark->bookmarkable->route->source }} 
                                    <span class="text-gray-400 mx-2">-></span>
                                    {{ $bookmark->bookmarkable->route->destination->city_name ?? $bookmark->bookmarkable->route->destination_code }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ $bookmark->bookmarkable->bus->bus_name ?? 'Bus' }} - Rp {{ number_format($bookmark->bookmarkable->price_per_seat, 0, ',', '.') }}
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('booking.create', ['schedule_id' => $bookmark->bookmarkable->id]) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Book Now &rarr;</a>
                                </div>
                            @elseif($bookmark->bookmarkable_type === 'App\Models\Booking')
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">Booking</span>
                                    <span class="text-sm text-gray-500">ID: {{ $bookmark->bookmarkable->id }}</span>
                                </div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ $bookmark->bookmarkable->schedule->route->sourceDestination->city_name ?? $bookmark->bookmarkable->schedule->route->source }}
                                    <span class="text-gray-400 mx-2">-></span>
                                    {{ $bookmark->bookmarkable->schedule->route->destination->city_name ?? $bookmark->bookmarkable->schedule->route->destination_code }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Status: {{ $bookmark->bookmarkable->status }}
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('booking.ticket', $bookmark->bookmarkable->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">View Ticket &rarr;</a>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Remove Bookmark Button -->
                        <button onclick="toggleBookmark(this, '{{ $bookmark->bookmarkable_id }}', '{{ addslashes($bookmark->bookmarkable_type) }}')" 
                                class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors" 
                                title="Remove from bookmarks">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <script>
                async function toggleBookmark(button, id, type) {
                    // Optimistic UI: Find parent container and hide/remove it
                    const container = button.closest('.bg-white');
                    container.style.opacity = '0.5';
                    container.style.pointerEvents = 'none';

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
                        
                        // Success: Remove element
                        container.remove();
                        
                        // Check if empty
                        const grid = document.querySelector('.grid.gap-6');
                        if(grid && grid.children.length === 0) {
                            window.location.reload(); // Reload to show empty state
                        }

                    } catch (e) {
                        console.error(e);
                        // Revert styling
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                        alert('Failed to remove bookmark.');
                    }
                }
            </script>
        @endif
    </div>
</div>
@endsection
