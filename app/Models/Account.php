<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_type_id', 'first_name', 'last_name', 
        'email', 'phone', 'birthdate', 'password_hash'
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}