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

    public static function getTopPerformingRoutes($limit = 5, $orderBy = 'total_revenue')
    {
        return \Illuminate\Support\Facades\DB::table('routes as r')
            ->join('destinations as d', 'r.destination_code', '=', 'd.code')
            ->leftJoin('schedules as s', 'r.id', '=', 's.route_id')
            ->leftJoin('bookings as b', function($join) {
                $join->on('s.id', '=', 'b.schedule_id')
                     ->where('b.status', '=', \App\Models\Booking::STATUS_BOOKED);
            })
            ->select([
                'r.id as route_id',
                \Illuminate\Support\Facades\DB::raw("CONCAT(r.source_code, ' - ', r.destination_code) as route_name"),
                'r.source as source_name',
                'd.city_name as destination_name',
                'r.destination_code',
                \Illuminate\Support\Facades\DB::raw('COUNT(b.id) as total_bookings'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(b.total_amount), 0) as total_revenue'),
                \Illuminate\Support\Facades\DB::raw('AVG(s.price_per_seat) as average_ticket_price')
            ])
            ->groupBy('r.id', 'r.source', 'r.source_code', 'r.destination_code', 'd.city_name')
            ->orderByDesc($orderBy)
            ->limit($limit)
            ->get();
    }
}