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
        'arrival_time', 'price_per_seat', 'quota', 'remarks'
    ];

    protected $with = ['route', 'bus'];

    public function getAvailableSeats($travelDate)
    {
        // 1. Count occupied seats for this specific date
        $occupied = Ticket::whereHas('booking', function($query) use ($travelDate) {
            $query->where('schedule_id', $this->id)
                  ->where('travel_date', $travelDate)
                  ->where('status', '!=', 'cancelled');
        })->count();

        // 2. Return the math
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

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scheduleDetails()
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}