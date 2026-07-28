<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePickupTimeRequest;
use App\Http\Requests\Admin\StoreScheduleOverrideRequest;
use App\Models\PickupTime;
use App\Models\PickupTimeOverride;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleSettingController extends Controller
{
    public function index(): Response
    {
        $pickupTimes = PickupTime::orderBy('sort_order', 'asc')->get();
        $overrides = PickupTimeOverride::with('pickupTime')
            ->where('override_date', '>=', now()->toDateString())
            ->orderBy('override_date', 'asc')
            ->get();

        return Inertia::render('Settings/Schedule', [
            'pickupTimes' => $pickupTimes,
            'overrides' => $overrides,
        ]);
    }

    public function storeSlot(StorePickupTimeRequest $request)
    {
        $validated = $request->validated();
        $maxOrder = PickupTime::max('sort_order') ?? 0;

        PickupTime::create([
            'time_label' => $validated['time_label'],
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Time slot created!');
    }

    public function toggleSlot(PickupTime $slot)
    {
        $slot->update(['is_active' => ! $slot->is_active]);

        return back()->with('success', 'Time slot status updated.');
    }

    public function destroySlot(PickupTime $slot)
    {
        $slot->delete();

        return back()->with('success', 'Time slot deleted.');
    }

    public function storeOverride(StoreScheduleOverrideRequest $request)
    {
        $validated = $request->validated();

        PickupTimeOverride::updateOrCreate([
            'pickup_time_id' => $validated['pickup_time_id'] ?? null,
            'override_date' => $validated['override_date'],
        ], [
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Schedule override saved!');
    }

    public function destroyOverride(PickupTimeOverride $override)
    {
        $override->delete();

        return back()->with('success', 'Schedule override removed.');
    }
}
