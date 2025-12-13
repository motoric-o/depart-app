<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    
    protected $fillable = ['code', 'city_name'];

    public function routes()
    {
        return $this->hasMany(Route::class, 'destination_code', 'code');
    }
}