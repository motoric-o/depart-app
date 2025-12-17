<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'bus_number', 'bus_type', 'capacity', 'quota', 
        'seat_rows', 'seat_columns', 'remarks'
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}