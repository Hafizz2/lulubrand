<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'url',
        'color_value',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get absolute URL for the image asset.
     */
    public function getUrlAttribute(?string $value): string
    {
        if (empty($value)) {
            return 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:image/')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }

    /**
     * Generate responsive srcset string for storefront images.
     */
    public function getSrcsetAttribute(): string
    {
        $url = $this->url;

        if (str_contains($url, 'images.unsplash.com')) {
            $base = strtok($url, '?');
            return "{$base}?auto=format&fit=crop&w=400&q=80 400w, "
                 . "{$base}?auto=format&fit=crop&w=800&q=80 800w, "
                 . "{$base}?auto=format&fit=crop&w=1200&q=80 1200w";
        }

        return "{$url} 800w";
    }
}
