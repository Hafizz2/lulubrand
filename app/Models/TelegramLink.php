<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'telegram_chat_id',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
