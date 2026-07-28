<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_homepage_loads_successfully(): void
    {
        $category = Category::create(['name' => 'Dresses', 'slug' => 'dresses']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Silk Evening Gown',
            'slug' => 'silk-evening-gown',
            'base_price' => 15000,
            'status' => 'published',
            'is_new' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Silk Evening Gown');
    }

    public function test_catalog_page_filters_and_returns_ajax_json(): void
    {
        $category = Category::create(['name' => 'Mini Dresses', 'slug' => 'mini-dresses']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Velvet Mini',
            'slug' => 'velvet-mini',
            'base_price' => 12000,
            'status' => 'published',
        ]);

        $response = $this->getJson('/catalog?category=mini-dresses');

        $response->assertStatus(200);
        $response->assertJsonStructure(['products', 'has_more', 'html']);
    }

    public function test_product_detail_page_loads_with_variants(): void
    {
        $category = Category::create(['name' => 'Gowns', 'slug' => 'gowns']);
        $product = Product::create([
            'category_id' => $category->id,
            'slug' => 'sculpted-gown',
            'title' => 'Sculpted Gown',
            'base_price' => 18000,
            'status' => 'published',
        ]);

        $sizeAttr = Attribute::create(['name' => 'Size', 'slug' => 'size']);
        $sizeValue = AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'M']);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SCULPTED-M',
            'stock_quantity' => 10,
        ]);
        $variant->attributeValues()->attach($sizeValue->id);

        $response = $this->get('/product/sculpted-gown');

        $response->assertStatus(200);
        $response->assertSee('Sculpted Gown');
        $response->assertSee('SCULPTED-M');
    }
}
