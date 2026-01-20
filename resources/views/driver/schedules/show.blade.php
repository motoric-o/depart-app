@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('driver.dashboard') }}" class="text-gray-500 hover:text-gray-700 mb-4 inline-block">&larr; Back to Dashboard</a>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-400">Schedule ID: #{{ $schedule->id }}</p>
                        <p class="text-gray-600 mt-1">Bus: {{ $schedule->bus->bus_number }} ({{ $schedule->bus->bus_type }})</p>
                        <p class="text-gray-600">Departure: {{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <!-- Bus Remarks -->
                <div class="mt-6 border-t pt-4">
                    <form action="{{ route('driver.schedules.remarks', $schedule->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Schedule Remarks</label>
                            <textarea name="remarks" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2" placeholder="e.g. Traffic update, passenger notes...">{{ $schedule->remarks }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bus Remarks (Condition)</label>
                            <textarea name="bus_remarks" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2" placeholder="e.g. AC maintenance needed, clean condition...">{{ $schedule->bus->remarks }}</textarea>
                        </div>

                        <div class="mt-2 text-right">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Save Remarks</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Passenger Manifest / Attendance -->
        <h4 class="text-lg font-bold mb-4 ml-1">Passenger Manifest</h4>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                @if($scheduleDetails->isEmpty())
                    <p class="text-gray-500">No confirmed bookings for this trip.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($scheduleDetails as $detail)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $detail->ticket?->id ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                            {{ $detail->seat_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $detail->ticket?->passenger_name ?? 'Vsitor' }}<br>
                                            <span class="text-xs">{{ $detail->ticket?->booking?->account?->phone ?? '-' }}</span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span id="status-{{ $detail->id }}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $detail->attendance_status === 'Present' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $detail->attendance_status === 'Present' ? 'Present' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button id="btn-{{ $detail->id }}" 
                                                onclick="toggleCheckIn('{{ $detail->id }}')" 
                                                class="{{ $detail->attendance_status === 'Present' ? 'text-gray-500 hover:text-gray-700' : 'text-blue-600 hover:text-blue-900 font-bold' }}">
                                                {{ $detail->attendance_status === 'Present' ? 'Undo Check-in' : 'Check In' }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCheckIn(detailId) {
        const btn = document.getElementById(`btn-${detailId}`);
        const statusSpan = document.getElementById(`status-${detailId}`);
        const originalText = btn.innerText;

        // Optimistic UI update or Loading state
        btn.innerText = 'Processing...';
        btn.disabled = true;

        fetch(`/driver/seats/${detailId}/check-in`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI based on new status
                if (data.new_status === 'Present') {
                    // Update Badge
                    statusSpan.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                    statusSpan.innerText = 'Present';
                    
                    // Update Button
                    btn.className = 'text-gray-500 hover:text-gray-700';
                    btn.innerText = 'Undo Check-in';
                } else {
                    // Update Badge
                    statusSpan.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
                    statusSpan.innerText = 'Pending';
                    
                    // Update Button
                    btn.className = 'text-blue-600 hover:text-blue-900 font-bold';
                    btn.innerText = 'Check In';
                }
            } else {
                alert('Something went wrong. Please try again.');
                btn.innerText = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error connecting to server.');
            btn.innerText = originalText;
        })
        .finally(() => {
            btn.disabled = false;
        });
    }
</script>
@endsection
