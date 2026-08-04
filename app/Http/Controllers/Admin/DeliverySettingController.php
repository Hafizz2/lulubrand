<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliverySettingController extends Controller
{
    public function index(): Response
    {
        $shippingRates = ShippingRate::orderBy('country')
            ->orderBy('city')
            ->orderBy('district')
            ->get();

        return Inertia::render('Settings/Delivery', [
            'shippingRates' => $shippingRates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        ShippingRate::create([
            'country' => $validated['country'],
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'cost_cents' => isset($validated['cost']) ? (int) round($validated['cost'] * 100) : 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Shipping rate added successfully.');
    }

    public function update(Request $request, ShippingRate $shippingRate)
    {
        $validated = $request->validate([
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $shippingRate->update([
            'country' => $validated['country'],
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'cost_cents' => isset($validated['cost']) ? (int) round($validated['cost'] * 100) : 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Shipping rate updated successfully.');
    }

    public function toggle(ShippingRate $shippingRate)
    {
        $shippingRate->update([
            'is_active' => !$shippingRate->is_active,
        ]);

        return back()->with('success', 'Shipping rate status toggled.');
    }

    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();

        return back()->with('success', 'Shipping rate deleted successfully.');
    }
}
