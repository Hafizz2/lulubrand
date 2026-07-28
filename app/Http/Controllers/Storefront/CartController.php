<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\ApplyDiscountRequest;
use App\Http\Requests\Storefront\UpdateCartRequest;
use App\Models\Discount;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $summary = $this->cartService->getCartSummary();
        return view('storefront.cart.index', compact('summary'));
    }

    public function summary()
    {
        return response()->json($this->cartService->getCartSummary());
    }

    public function add(AddToCartRequest $request)
    {
        $validated = $request->validated();

        $this->cartService->addItem($validated['product_variant_id'], $validated['quantity'] ?? 1);

        $summary = $this->cartService->getCartSummary();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to bag.',
                'summary' => $summary,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item added to your shopping bag.');
    }

    public function update(UpdateCartRequest $request, int $id)
    {
        $validated = $request->validated();

        $this->cartService->updateQuantity($id, $validated['quantity']);
        $summary = $this->cartService->getCartSummary();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function remove(Request $request, int $id)
    {
        $this->cartService->removeItem($id);
        $summary = $this->cartService->getCartSummary();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from bag.');
    }

    public function applyDiscount(ApplyDiscountRequest $request)
    {
        $validated = $request->validated();

        $discount = Discount::where('code', strtoupper($validated['code']))
            ->where('is_active', true)
            ->first();

        if (! $discount || ($discount->expires_at && $discount->expires_at->isPast())) {
            return back()->withErrors(['code' => 'Invalid or expired coupon code.']);
        }

        Session::put('applied_discount_code', $discount->code);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Coupon {$discount->code} applied!",
                'summary' => $this->cartService->getCartSummary(),
            ]);
        }

        return redirect()->route('cart.index')->with('success', "Coupon {$discount->code} applied successfully!");
    }
}
