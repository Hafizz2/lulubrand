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
        'images',
        'status',
        'product_ids',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'images' => 'array',
    ];

    protected $appends = ['image_url', 'images_urls'];

    /**
     * Get absolute URLs for all outfit gallery images.
     */
    public function getImagesUrlsAttribute(): array
    {
        if (empty($this->images)) {
            return [];
        }

        return array_map(function($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:image/')) {
                return $path;
            }
            $cleanPath = ltrim($path, '/');
            if (!str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = 'storage/' . $cleanPath;
            }
            return asset($cleanPath);
        }, $this->images);
    }

    /**
     * Get absolute URL for outfit image.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return asset('logo.png');
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
