<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_id', 'booking_id', 'ticket_id', 
        'transaction_date', 'payment_method', 'sub_total', 
        'total_amount', 'type', 'status'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function paymentIssueProofs()
    {
        return $this->hasMany(PaymentIssueProof::class);
    }

    public function expense()
    {
        return $this->hasOne(Expense::class);
    }
}