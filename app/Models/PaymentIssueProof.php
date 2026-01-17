<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentIssueProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'file_path',
        'message',
        'sender_type',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
