<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class BookingController extends Controller
{
    use \App\Traits\ApiSearchable;

    public function index(Request $request)
    {
        // \Illuminate\Support\Facades\Gate::authorize('manage-bookings'); // Add Gate later if needed
        $query = \App\Models\Booking::with(['account', 'schedule.route.sourceDestination', 'schedule.route.destination', 'schedule.bus']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('account', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        
        $allowed = ['id', 'booking_date', 'travel_date', 'total_amount', 'status', 'created_at'];
        if(in_array($sort_by, $allowed)){
            $query->orderBy($sort_by, $sort_order);
        } else {
             $query->orderBy('created_at', 'desc');
        }

        $bookings = $query->paginate(10);
        return response()->json($bookings);
    }

    public function destroy($id)
    {
        // \Illuminate\Support\Facades\Gate::authorize('manage-bookings');
        $booking = \App\Models\Booking::findOrFail($id);
        
        \App\Models\Ticket::where('booking_id', $booking->id)->delete();
        \App\Models\Transaction::where('booking_id', $booking->id)->delete();
        
        $booking->delete();
        return response()->json(null, 204);
    }
}
