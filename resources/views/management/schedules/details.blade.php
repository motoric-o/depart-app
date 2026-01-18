@extends('layouts.app')

@section('content')
<div class="py-12" x-data="scheduleDetailsParam({{ $schedule->id ? "'{$schedule->id}'" : 'null' }})">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="mb-4 flex flex-col md:flex-row justify-between items-center">
                    <div>
                        <a href="{{ route('admin.schedules') }}" class="text-gray-600 hover:text-gray-900 mb-2 inline-block">&larr; Back to Schedules</a>
                        <h2 class="text-2xl font-bold" x-text="'Schedule Details: ' + (schedule ? schedule.id : 'Loading...')"></h2>
                        <p class="text-gray-500" x-show="schedule">
                            Route: <span x-text="schedule?.route?.source + ' -> ' + schedule?.route?.destination?.city_name"></span> | 
                            Bus: <span x-text="schedule?.bus?.bus_number"></span>
                        </p>
                    </div>
                </div>

                <!-- Messages -->
                <div x-show="message" class="mb-4 px-4 py-3 rounded relative" :class="messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'">
                    <span x-text="message"></span>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-8">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-gray-500">Loading details...</p>
                </div>

                <!-- Content -->
                <div x-show="!loading" style="display: none;">
                    
                    <!-- Toolbar -->
                    {{-- <div class="mb-4 flex justify-end">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Seat (Manual)</button>
                    </div> --}}

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket / Booking</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="detail in details" :key="detail.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="detail.sequence"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600" x-text="detail.seat_number || '-'"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span x-text="detail.ticket_id ? detail.ticket_id : '-'"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="detail.passenger_name"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                  :class="{
                                                      'bg-green-100 text-green-800': detail.attendance_status === 'Present',
                                                      'bg-yellow-100 text-yellow-800': detail.attendance_status === 'Pending',
                                                      'bg-red-100 text-red-800': detail.attendance_status === 'Absent'
                                                  }"
                                                  x-text="detail.attendance_status === 'Present' ? 'Present' : (detail.attendance_status === 'Pending' ? 'Pending' : (detail.attendance_status === 'Absent' ? 'Absent' : detail.attendance_status))">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="detail.remarks || '-'"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="toggleStatus(detail)" 
                                                class="mr-3 font-bold"
                                                :class="detail.attendance_status === 'Present' ? 'text-gray-500 hover:text-gray-700' : 'text-blue-600 hover:text-blue-900'"
                                                x-text="detail.attendance_status === 'Present' ? 'Cancel Check-in' : 'Check In'">
                                            </button>
                                            <button @click="openEditModal(detail)" class="text-blue-600 hover:text-blue-900">Edit</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="details.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No details found (Seats not generated?).</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="isEditModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="isEditModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="closeEditModal">
                <div class="absolute inset-0 bg-gray-500/75 backdrop-blur-sm"></div>
            </div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="isEditModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Detail</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Seat Number</label>
                            <input type="text" x-model="editForm.seat_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Attendance Status</label>
                            <select x-model="editForm.attendance_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="Pending">Pending</option>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remarks</label>
                            <textarea x-model="editForm.remarks" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="updateDetail" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" @click="closeEditModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function scheduleDetailsParam(initialId) {
        return {
            scheduleId: initialId,
            schedule: null,
            details: [],
            loading: true,
            message: '',
            messageType: '', // 'success' or 'error'
            
            isEditModalOpen: false,
            editForm: {
                id: null,
                ticket_id: null, // Added
                seat_number: '',
                attendance_status: 'Pending',
                remarks: ''
            },

            init() {
                if(this.scheduleId) {
                    this.fetchDetails();
                }
            },

            fetchDetails() {
                this.loading = true;
                this.message = '';
                
                fetch(`/api/schedules/${this.scheduleId}/details`, {
                    headers: {
                        'Accept': 'application/json',
                        // If auth needed, logic to add token here
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load details');
                    return response.json();
                })
                .then(data => {
                    this.schedule = data.schedule;
                    this.details = data.details;
                    this.loading = false;
                })
                .catch(error => {
                    this.message = error.message;
                    this.messageType = 'error';
                    this.loading = false;
                });
            },

            openEditModal(detail) {
                this.editForm.id = detail.id;
                this.editForm.ticket_id = detail.ticket_id; // Added
                this.editForm.seat_number = detail.seat_number;
                this.editForm.attendance_status = detail.attendance_status;
                this.editForm.remarks = detail.remarks;
                this.isEditModalOpen = true;
            },

            closeEditModal() {
                this.isEditModalOpen = false;
            },

            updateDetail() {
                const id = this.editForm.id;
                
                fetch(`/api/schedules/details/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ticket_id: this.editForm.ticket_id, // Added
                        seat_number: this.editForm.seat_number,
                        attendance_status: this.editForm.attendance_status,
                        remarks: this.editForm.remarks
                    })
                })
                .then(response => {
                     // Check status. If 200, success.
                     return response.json().then(data => ({ status: response.status, body: data }));
                })
                .then(({ status, body }) => {
                    if (status !== 200) {
                        throw new Error(body.message || 'Update failed');
                    }
                    this.message = 'Detail updated successfully.';
                    this.messageType = 'success';
                    this.closeEditModal();
                    this.fetchDetails(); // Reload
                })
                .catch(error => {
                    this.message = error.message;
                    this.messageType = 'error';
                });
            },

            toggleStatus(detail) {
                const newStatus = detail.attendance_status === 'Present' ? 'Pending' : 'Present';
                const id = detail.id;

                // Optimistic UI Update
                const oldStatus = detail.attendance_status;
                detail.attendance_status = newStatus;

                fetch(`/api/schedules/details/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ticket_id: detail.ticket_id,
                        seat_number: detail.seat_number,
                        attendance_status: newStatus,
                        remarks: detail.remarks
                    })
                })
                .then(response => {
                     return response.json().then(data => ({ status: response.status, body: data }));
                })
                .then(({ status, body }) => {
                    if (status !== 200) {
                         // Revert if failed
                         detail.attendance_status = oldStatus;
                        throw new Error(body.message || 'Update failed');
                    }
                    // Success (maybe show toast?)
                    this.message = 'Status updated.';
                    this.messageType = 'success';
                    // No need to reload logic here if optimistic worked, but verification is safer:
                    // this.fetchDetails(); 
                    // Let's rely on optimistic for speed as per user request.
                })
                .catch(error => {
                    // Revert
                    detail.attendance_status = oldStatus;
                    this.message = error.message;
                    this.messageType = 'error';
                });
            }
        }
    }
</script>
@endsection
