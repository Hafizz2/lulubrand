<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outfit extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'status',
        'product_ids',
    ];

    protected $casts = [
        'product_ids' => 'array',
    ];

    protected $appends = ['image_url'];

    /**
     * Get absolute URL for outfit image.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80';
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://') || str_starts_with($this->image_path, 'data:image/')) {
            return $this->image_path;
        }

        $cleanPath = ltrim($this->image_path, '/');
        if (!str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = 'storage/' . $cleanPath;
        }

        return asset($cleanPath);
    }

    /**
     * Get products in this outfit look.
     */
    public function getProductsAttribute()
    {
        if (empty($this->product_ids)) {
            return collect();
        }
        return Product::whereIn('id', $this->product_ids)
            ->where('status', 'published')
            ->with(['primaryImage', 'images', 'category', 'variants.attributeValues.attribute', 'variants.image'])
            ->get();
    }
}
