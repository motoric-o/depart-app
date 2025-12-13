<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['booking_id', 'passenger_name', 'seat_number', 'status'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}