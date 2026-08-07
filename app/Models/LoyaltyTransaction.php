<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'points',
        'source',
        'reference_id',
        'reference_type',
        'purchase_amount_cents',
        'description',
        'staff_id',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'purchase_amount_cents' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
