<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_type_id', 'first_name', 'last_name', 
        'email', 'phone', 'birthdate', 'password_hash'
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'user_id');
    }

    protected static function booted()
    {
        static::deleting(function ($account) {
            // Update schedules where this account is the driver
            \App\Models\Schedule::where('driver_id', $account->id)->update([
                'driver_id' => null,
                'remarks' => 'Pending Driver Assignment'
            ]);
        });
    }
}