<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScheduleDetail;
use App\Models\Schedule;

class ScheduleDetailController extends Controller
{
    /**
     * Get all details for a specific schedule.
     */
    public function index($scheduleId)
    {
        // We can use Eloquent for reading (as per typical CQRS-lite or practical implementation) 
        // OR a Stored Procedure if strict. 
        // The user said "make the crud stored in a stored procedure", usually meaning writes/mutations.
        // Reading via Eloquent is usually acceptable unless specified otherwise.
        // Let's use Eloquent with relationships for the Index to get Ticket/Passenger names easily.
        
        $details = ScheduleDetail::with(['ticket.booking.account']) // Nested eager load to get passenger info if linked
            ->where('schedule_id', $scheduleId)
            ->orderBy('sequence')
            ->get();
            
        // Transform for API
        $data = $details->map(function($detail) {
            $passengerName = 'N/A';
            if ($detail->ticket) {
                // Priority: Ticket name, then Booking Account name, using NullSafe operator
                $passengerName = $detail->ticket->passenger_name 
                                 ?? ($detail->ticket->booking?->account?->first_name . ' ' . $detail->ticket->booking?->account?->last_name ?? 'N/A');
            }
            
            return [
                'id' => $detail->id,
                'sequence' => $detail->sequence,
                'ticket_id' => $detail->ticket_id,
                'seat_number' => $detail->seat_number, // Added
                'passenger_name' => $passengerName,
                'attendance_status' => $detail->attendance_status,
                'remarks' => $detail->remarks,
            ];
        });

        // Also fetch Schedule info for header
        $schedule = Schedule::with('bus', 'route.destination')->find($scheduleId);

        return response()->json([
            'schedule' => $schedule,
            'details' => $data
        ]);
    }

    /**
     * Store a new Schedule Detail (Manual Entry).
     */
    public function store(Request $request, $scheduleId)
    {
        $request->validate([
            'sequence' => 'required|integer',
            'ticket_id' => 'nullable|exists:tickets,id',
            'seat_number' => 'nullable|string',
            'attendance_status' => 'required|in:Pending,Present,Absent',
            'remarks' => 'nullable|string',
        ]);

        DB::statement("CALL sp_manage_schedule_detail('CREATE', NULL, ?, ?, ?, ?, ?, ?)", [
            $scheduleId,
            $request->sequence,
            $request->ticket_id,
            $request->seat_number,
            $request->attendance_status,
            $request->remarks
        ]);

        return response()->json(['message' => 'Detail created successfully']);
    }

    /**
     * Update an existing Schedule Detail.
     */
    public function update(Request $request, $detailId)
    {
        $request->validate([
            'ticket_id' => 'nullable|string', // Basic validation
            'seat_number' => 'nullable|string',
            'attendance_status' => 'required|in:Pending,Present,Absent',
            'remarks' => 'nullable|string',
        ]);

        DB::statement("CALL sp_manage_schedule_detail('UPDATE', ?, NULL, NULL, ?, ?, ?, ?)", [
            $detailId,
            $request->ticket_id,
            $request->seat_number,
            $request->attendance_status,
            $request->remarks
        ]);

        return response()->json(['message' => 'Detail updated successfully']);
    }

    /**
     * Delete a Schedule Detail.
     */
    public function destroy($detailId)
    {
        DB::statement("CALL sp_manage_schedule_detail('DELETE', ?, NULL, NULL, NULL, NULL, NULL)", [
            $detailId
        ]);

        return response()->json(['message' => 'Detail deleted successfully']);
    }
}
