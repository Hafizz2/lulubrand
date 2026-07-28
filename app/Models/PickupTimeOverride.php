<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupTimeOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_time_id',
        'override_date',
        'status',
    ];

    protected $casts = [
        'override_date' => 'date:Y-m-d',
    ];

    public function pickupTime()
    {
        return $this->belongsTo(PickupTime::class);
    }
}
