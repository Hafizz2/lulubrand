<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutConcurrentStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_orders_competing_for_last_unit_of_stock_only_one_succeeds(): void
    {
        // 1. Create a product with exactly 1 unit in stock
        $category = Category::create(['name' => 'Dresses', 'slug' => 'dresses']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Limited Edition Gown',
            'slug' => 'limited-gown',
            'base_price' => 20000,
            'status' => 'published',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'LIMITED-GOWN-S',
            'stock_quantity' => 1,
        ]);

        // 2. First Customer adds item to cart & places order
        $this->post('/cart/add', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response1 = $this->post('/checkout', [
            'customer_name' => 'First Customer',
            'customer_phone' => '+251911111111',
            'customer_address' => 'Bole, Addis Ababa',
            'customer_city' => 'Addis Ababa',
            'logistics_mode' => 'pickup',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:00 AM - 12:00 PM',
            'payment_method' => 'cod',
        ]);

        // First order must succeed
        $response1->assertRedirect();
        $this->assertDatabaseHas('orders', ['customer_name' => 'First Customer']);

        // Stock must now be 0
        $variant->refresh();
        $this->assertEquals(0, $variant->stock_quantity);

        // 3. Second Customer attempts to order the same variant
        // Reset session/cart context for second user
        $this->flushSession();

        $this->post('/cart/add', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response2 = $this->post('/checkout', [
            'customer_name' => 'Second Customer',
            'customer_phone' => '+251922222222',
            'customer_address' => 'Kazanchis, Addis Ababa',
            'customer_city' => 'Addis Ababa',
            'logistics_mode' => 'pickup',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:00 AM - 12:00 PM',
            'payment_method' => 'cod',
        ]);

        // Second order must fail gracefully with error and NOT create an order
        $response2->assertSessionHasErrors(['checkout']);
        $this->assertDatabaseMissing('orders', ['customer_name' => 'Second Customer']);

        // Stock remains 0 and never drops below zero
        $variant->refresh();
        $this->assertEquals(0, $variant->stock_quantity);
    }
}
