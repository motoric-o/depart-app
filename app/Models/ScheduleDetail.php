<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDetail extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'schedule_id', 'sequence', 
        'ticket_id', 'seat_number', 'attendance_status', 'remarks'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->sequence) {
                // Determine next sequence for this schedule
                $maxSeq = static::where('schedule_id', $model->schedule_id)->max('sequence');
                $model->sequence = $maxSeq ? $maxSeq + 1 : 1;
            }
            // Generate ID: {schedule_id}-{sequence}
            $model->id = $model->schedule_id . '-' . $model->sequence;
        });
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
