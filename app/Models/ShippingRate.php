<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        'city',
        'district',
        'cost_cents',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost_cents' => 'integer',
    ];
}
