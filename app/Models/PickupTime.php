<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function overrides()
    {
        return $this->hasMany(PickupTimeOverride::class);
    }
}
