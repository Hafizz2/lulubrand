<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_revenue' => number_format(
                Order::where('payment_status', 'paid')->sum('total') / 100,
                2
            ),
            'low_stock' => ProductVariant::where('stock_quantity', '<=', 5)->count(),
            'recent_orders' => Order::latest()
                ->take(7)
                ->get(['id', 'order_number', 'customer_name', 'total', 'status'])
                ->toArray(),
        ];

        return Inertia::render('Dashboard', ['stats' => $stats]);
    }
}
