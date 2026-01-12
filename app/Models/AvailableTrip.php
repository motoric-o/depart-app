<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableTrip extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'view_available_trips';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'schedule_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $guarded = ['*']; 
    // It's a view, so logically it should be read-only, ensuring no writes happen via this model.
}
