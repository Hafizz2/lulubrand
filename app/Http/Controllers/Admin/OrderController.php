<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(20)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'payment_status', 'search', 'date_from', 'date_to']),
            'statusOptions' => ['pending', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled', 'refunded'],
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items.variant.attributeValues', 'items.product.primaryImage', 'bankAccount', 'user']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $validated = $request->validated();

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        OrderStatusChanged::dispatch($order, $oldStatus, $validated['status']);

        return back()->with('success', "Order #{$order->order_number} status updated to {$validated['status']}.");
    }

    public function markPaid(Request $request, Order $order)
    {
        $order->update(['payment_status' => 'paid']);

        return back()->with('success', "Order #{$order->order_number} marked as paid.");
    }

    public function invoice(Order $order)
    {
        $order->load(['items.variant.attributeValues', 'items.product']);

        return Inertia::render('Orders/Invoice', [
            'order' => $order,
        ]);
    }
}
