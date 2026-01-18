<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['source', 'source_code', 'destination_code', 'distance', 'estimated_duration'];

    protected $with = ['destination'];

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_code', 'code');
    }

    public function sourceDestination()
    {
        return $this->belongsTo(Destination::class, 'source_code', 'code');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    protected static function booted()
    {
        static::deleting(function ($route) {
            $route->load('schedules', 'destination'); // Ensure relations are loaded
            
            foreach ($route->schedules as $schedule) {
                $schedule->route_source = $route->source;
                $schedule->route_destination = $route->destination->city_name ?? $route->destination_code;
                $schedule->route_id = null;
                $schedule->remarks = 'Canceled';
                $schedule->save();
            }
        });
    }
}