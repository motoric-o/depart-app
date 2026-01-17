<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'bus_number', 'bus_name', 'bus_type', 'capacity', 
        'seat_rows', 'seat_columns', 'remarks'
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    protected static function booted()
    {
        static::deleting(function ($bus) {
            $bus->schedules()->update([
                'bus_id' => null, 
                'remarks' => 'Pending Bus Assignment'
            ]);
        });
    }
}