<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifiedTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'transaction_id',
        'amount',
        'order_id',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
