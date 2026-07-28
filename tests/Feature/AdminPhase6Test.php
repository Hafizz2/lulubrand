<?php

namespace Tests\Feature;

use App\Events\OrderStatusChanged;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminPhase6Test extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Owner',
            'email' => 'owner@lulu.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);
    }

    // ── Categories ────────────────────────────────────────────────
    public function test_admin_can_create_a_category(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Evening Gowns',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Evening Gowns', 'slug' => 'evening-gowns']);
    }

    public function test_admin_can_delete_a_category(): void
    {
        $admin = $this->makeAdmin();
        $category = \App\Models\Category::create(['name' => 'Temp', 'slug' => 'temp']);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // ── Orders ────────────────────────────────────────────────────
    public function test_admin_can_change_order_status_and_event_is_dispatched(): void
    {
        Event::fake([OrderStatusChanged::class]);
        $admin = $this->makeAdmin();

        $order = Order::create([
            'order_number' => 'LULU-TEST01',
            'customer_name' => 'Test Customer',
            'customer_phone' => '+251911',
            'customer_address' => 'Test Address',
            'customer_city' => 'Addis Ababa',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'subtotal' => 10000,
            'discount_amount' => 0,
            'total' => 10000,
        ]);

        $response = $this->actingAs($admin)->post("/admin/orders/{$order->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
        Event::assertDispatched(OrderStatusChanged::class);
    }

    // ── Discounts ─────────────────────────────────────────────────
    public function test_admin_can_create_a_percentage_discount_code(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post('/admin/discounts', [
            'code' => 'LULU20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('discounts', ['code' => 'LULU20', 'type' => 'percentage']);
    }

    public function test_admin_can_toggle_discount_status(): void
    {
        $admin = $this->makeAdmin();
        $discount = Discount::create([
            'code' => 'SUMMER10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post("/admin/discounts/{$discount->id}/toggle");

        $discount->refresh();
        $this->assertFalse($discount->is_active);
    }

    // ── Stock ─────────────────────────────────────────────────────
    public function test_admin_can_adjust_variant_stock_and_log_is_created(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Tops', 'slug' => 'tops']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Silk Blouse',
            'slug' => 'silk-blouse',
            'base_price' => 8000,
            'status' => 'published',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SILK-BLK-M',
            'stock_quantity' => 5,
        ]);

        $this->actingAs($admin)->post("/admin/stock/{$variant->id}/adjust", [
            'delta' => 10,
            'reason' => 'restock',
        ]);

        $variant->refresh();
        $this->assertEquals(15, $variant->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'delta' => 10,
            'resulting_quantity' => 15,
            'reason' => 'restock',
        ]);
    }
}
