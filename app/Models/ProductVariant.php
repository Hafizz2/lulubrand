<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price_override',
        'stock_quantity',
        'image_id',
    ];

    protected $casts = [
        'price_override' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function image()
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_value');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getEffectivePriceAttribute(): int
    {
        return $this->price_override ?? $this->product->base_price;
    }
}
