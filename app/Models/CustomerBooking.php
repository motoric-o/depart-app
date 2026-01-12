<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBooking extends Model
{
    protected $table = 'view_customer_bookings';
    public $incrementing = false;
    protected $primaryKey = 'booking_id';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = ['*']; 
    
    // Optional: Casts
    protected $casts = [
        'booking_date' => 'datetime',
        'travel_date' => 'date',
        'departure_time' => 'datetime',
        'seat_count' => 'integer',
        'total_amount' => 'decimal:2',
    ];
}
