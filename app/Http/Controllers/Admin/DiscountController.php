<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDiscountRequest;
use App\Models\Discount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->paginate(20);

        return Inertia::render('Discounts/Index', [
            'discounts' => $discounts,
        ]);
    }

    public function store(StoreDiscountRequest $request)
    {
        $validated = $request->validated();

        Discount::create([
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'value' => $validated['type'] === 'fixed'
                ? (int) round($validated['value'] * 100)
                : (float) $validated['value'],
            'min_spend' => isset($validated['min_spend'])
                ? (int) round($validated['min_spend'] * 100)
                : null,
            'max_uses' => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', "Discount code {$validated['code']} created.");
    }

    public function toggle(Discount $discount)
    {
        $discount->update(['is_active' => !$discount->is_active]);

        return back()->with('success', 'Discount status updated.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return back()->with('success', 'Discount deleted.');
    }
}
