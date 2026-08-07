<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_country',
        'customer_district',
        'status',
        'payment_method',
        'payment_status',
        'logistics_mode',
        'delivery_fee',
        'preferred_date',
        'preferred_time',
        'google_maps_link',
        'payment_proof',
        'confirmed_transaction_id',
        'bank_account_id',
        'deposit_amount',
        'balance_due',
        'subtotal',
        'discount_amount',
        'total',
        'telegram_chat_id',
        'notes',
        'loyalty_points_earned',
        'loyalty_points_redeemed',
        'loyalty_discount_cents',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'delivery_fee' => 'integer',
        'deposit_amount' => 'integer',
        'balance_due' => 'integer',
        'total' => 'integer',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_redeemed' => 'integer',
        'loyalty_discount_cents' => 'integer',
        'preferred_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function verifiedTransaction()
    {
        return $this->hasOne(VerifiedTransaction::class);
    }
}
