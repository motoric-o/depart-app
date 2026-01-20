<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'route_id', 'bus_id', 'departure_time', 
        'arrival_time', 'price_per_seat', 'quota', 'remarks',
        'route_source', 'route_destination'
    ];

    protected $with = ['route', 'bus', 'driver'];

    public function getAvailableSeats($travelDate)
    {
        // Robust Method: Filter in PHP to match BookingController logic
        
        // 1. Fetch ALL bookings for this schedule
        $allBookings = Booking::where('schedule_id', $this->id)->get();

        // 2. Filter valid bookings
        $validBookingIds = $allBookings->filter(function($b) use ($travelDate) {
            // Compare Date (String)
            $dateMatch = substr($b->travel_date, 0, 10) === substr($travelDate, 0, 10);
            
            // Compare Status
            $s = trim($b->status);
            $statusMatch = in_array($s, ['Booked', 'Pending Payment', 'Confirmed', 'Pending']);

            return $dateMatch && $statusMatch;
        })->pluck('id');

        // 3. Count Occupied Seats (Tickets)
        $occupied = Ticket::whereIn('booking_id', $validBookingIds)
                          ->where('status', '!=', 'Cancelled')
                          ->count();

        if (!$this->bus) {
             return 0;
        }

        return $this->bus->capacity - $occupied;
    }
    
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver()
    {
        return $this->belongsTo(Account::class, 'driver_id');
    }

    public function scheduleDetails()
    {
        return $this->hasMany(ScheduleDetail::class);
    }


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function isBookmarkedBy($user)
    {
        if (!$user) return false;
        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }
}