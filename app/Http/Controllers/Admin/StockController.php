<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductVariant::with(['product', 'product.primaryImage'])
            ->orderBy('stock_quantity', 'asc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('low_only') && $request->input('low_only') == '1') {
            $query->where('stock_quantity', '<=', 5);
        }

        $variants = $query->paginate(30)->withQueryString();

        $movements = StockMovement::with(['variant.product', 'actor'])
            ->latest()
            ->take(30)
            ->get();

        return Inertia::render('Stock/Index', [
            'variants' => $variants,
            'movements' => $movements,
            'filters' => $request->only(['search', 'low_only']),
        ]);
    }

    public function adjust(AdjustStockRequest $request, ProductVariant $variant)
    {
        $validated = $request->validated();

        $delta = (int) $validated['delta'];
        $newQty = max(0, $variant->stock_quantity + $delta);

        $variant->update(['stock_quantity' => $newQty]);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'delta' => $delta,
            'resulting_quantity' => $newQty,
            'reason' => $validated['reason'],
            'actor_id' => Auth::id(),
        ]);

        // Trigger low-stock alert if stock drops to threshold
        if ($newQty <= 5) {
            $variant->refresh();
            $alertEvent = new \stdClass();
            $alertEvent->variant = $variant;
            app(\App\Listeners\SendLowStockAlert::class)->handle($alertEvent);
        }

        return back()->with('success', "Stock for {$variant->sku} adjusted by {$delta}. New qty: {$newQty}.");
    }
}
