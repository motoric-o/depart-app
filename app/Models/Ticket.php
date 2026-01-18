<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'booking_id', 'transaction_id', 'passenger_name', 'seat_number', 'status'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}