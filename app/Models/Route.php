<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['source', 'destination_code', 'distance', 'estimated_duration'];

    protected $with = ['destination'];

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_code', 'code');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}