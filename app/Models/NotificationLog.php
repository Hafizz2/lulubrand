<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notifications_log';

    protected $fillable = [
        'recipient',
        'channel',
        'event_type',
        'message_body',
        'status',
        'error_details',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
