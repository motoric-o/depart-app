<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;


    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'account_id', 'schedule_id', 'booking_date', 'travel_date',
        'total_amount', 'status'
    ];

    protected $with = ['tickets', 'schedule'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function payment()
    {
        return $this->hasOne(Transaction::class)->latestOfMany('created_at');
    }
    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function isBookmarkedBy($user)
    {
        if (!$user) return false;
        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }
}