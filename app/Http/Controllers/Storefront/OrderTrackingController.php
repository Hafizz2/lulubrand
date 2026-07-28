<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\OrderLookupRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product.primaryImage', 'items.variant.image', 'bankAccount'])
            ->firstOrFail();

        return view('storefront.checkout.confirmation', compact('order'));
    }

    public function showLookupForm()
    {
        return view('storefront.checkout.lookup');
    }

    public function lookup(OrderLookupRequest $request)
    {
        $validated = $request->validated();

        $order = Order::where('order_number', strtoupper(trim($validated['order_number'])))
            ->where('customer_phone', trim($validated['phone']))
            ->first();

        if (! $order) {
            return back()->withErrors(['lookup' => 'No order found matching this Order Number and Phone Number.'])->withInput();
        }

        return redirect()->route('order.confirmation', $order->order_number);
    }
}
