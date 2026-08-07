<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'product_code',
        'related_product_codes',
        'bundle_product_codes',
        'slug',
        'description',
        'material',
        'base_price',
        'status',
        'is_new',
        'is_presale',
        'presale_available_at',
        'presale_note',
        'published_at',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'is_presale' => 'boolean',
        'presale_available_at' => 'datetime',
        'published_at' => 'datetime',
        'base_price' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
