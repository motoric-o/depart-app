<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

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
}