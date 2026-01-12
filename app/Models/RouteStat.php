<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStat extends Model
{
    protected $table = 'view_route_stats';
    public $incrementing = false;
    protected $primaryKey = 'route_id';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = ['*']; 
    
    // Optional: Casts
    protected $casts = [
        'total_bookings' => 'integer',
        'total_revenue' => 'decimal:2',
        'average_ticket_price' => 'decimal:2',
    ];
}
