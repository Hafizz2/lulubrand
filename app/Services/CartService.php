<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart(): Cart
    {
        $userId = Auth::id();

        if ($userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            Session::put('cart_id', $cart->id);
            return $cart;
        }

        $cartId = Session::get('cart_id');
        if ($cartId) {
            $cart = Cart::find($cartId);
            if ($cart) {
                return $cart;
            }
        }

        $sessionId = Session::getId();
        $cart = Cart::create(['session_id' => $sessionId]);
        Session::put('cart_id', $cart->id);

        return $cart;
    }

    public function addItem(int $productVariantId, int $quantity = 1): CartItem
    {
        $cart = $this->getCart();
        $variant = ProductVariant::findOrFail($productVariantId);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
        }

        return $cartItem;
    }

    public function updateQuantity(int $cartItemId, int $quantity): ?CartItem
    {
        $cart = $this->getCart();
        $cartItem = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->first();

        if (! $cartItem) {
            return null;
        }

        if ($quantity <= 0) {
            $cartItem->delete();
            return null;
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return $cartItem;
    }

    public function removeItem(int $cartItemId): bool
    {
        $cart = $this->getCart();
        return (bool) CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->delete();
    }

    public function clearCart(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        Session::forget(['applied_discount_code', 'cart_id']);
    }

    public function getCartSummary(): array
    {
        $cart = $this->getCart();
        $cart->load(['items.variant.product', 'items.variant.image', 'items.variant.attributeValues.attribute']);

        $subtotal = 0;
        $itemsData = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            if (! $variant || ! $variant->product) continue;

            $unitPrice = $variant->price_override ?? $variant->product->base_price;
            $itemTotal = $unitPrice * $item->quantity;
            $subtotal += $itemTotal;

            $itemsData[] = [
                'id' => $item->id,
                'variant_id' => $variant->id,
                'title' => $variant->product->title,
                'sku' => $variant->sku,
                'attributes' => $variant->attributeValues->pluck('value')->implode(' / '),
                'unit_price' => $unitPrice,
                'unit_price_formatted' => number_format($unitPrice / 100, 2) . ' Birr',
                'quantity' => $item->quantity,
                'total_price' => $itemTotal,
                'total_price_formatted' => number_format($itemTotal / 100, 2) . ' Birr',
                'image_url' => $variant->image ? $variant->image->url : ($variant->product->primaryImage ? $variant->product->primaryImage->url : ''),
                'product_url' => route('catalog.show', $variant->product->slug),
            ];
        }

        // Apply Discount if Code present in Session
        $discountAmount = 0;
        $discountCode = Session::get('applied_discount_code');
        if ($discountCode) {
            $discount = Discount::where('code', $discountCode)->where('is_active', true)->first();
            if ($discount && ($discount->min_spend === null || $subtotal >= $discount->min_spend)) {
                if ($discount->type === 'percentage') {
                    $discountAmount = (int) round(($subtotal * $discount->value) / 100);
                } else {
                    $discountAmount = min($subtotal, $discount->value);
                }
            }
        }

        $total = max(0, $subtotal - $discountAmount);

        return [
            'cart_id' => $cart->id,
            'items' => $itemsData,
            'count' => array_sum(array_column($itemsData, 'quantity')),
            'subtotal' => $subtotal,
            'subtotal_formatted' => number_format($subtotal / 100, 2) . ' Birr',
            'discount_amount' => $discountAmount,
            'discount_amount_formatted' => number_format($discountAmount / 100, 2) . ' Birr',
            'discount_code' => $discountCode,
            'total' => $total,
            'total_formatted' => number_format($total / 100, 2) . ' Birr',
        ];
    }
}
