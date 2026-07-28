<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSizeGuideRequest;
use App\Models\SizeGuide;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SizeGuideSettingController extends Controller
{
    public function index(): Response
    {
        $sizeGuides = SizeGuide::orderBy('sort_order', 'asc')->get();

        $settings = [
            'size_guide_title' => SystemSetting::get('size_guide_title', 'LULU Couture Size Guide'),
            'size_guide_description' => SystemSetting::get('size_guide_description', "How to measure your size accurately:\n\n• Bust: Measure around the fullest part of your bust, keeping the tape horizontal.\n• Waist: Measure around your natural waistline, keeping the tape comfortably loose.\n• Hips: Stand with your feet together and measure around the fullest part of your hips.\n• Length: Measure from high point of shoulder seam down to bottom hem."),
            'size_guide_unit' => SystemSetting::get('size_guide_unit', 'Inches (in)'),
        ];

        return Inertia::render('Settings/SizeGuide', [
            'sizeGuides' => $sizeGuides,
            'settings' => $settings,
        ]);
    }

    public function store(StoreSizeGuideRequest $request)
    {
        $validated = $request->validated();
        $maxOrder = SizeGuide::max('sort_order') ?? 0;

        SizeGuide::create([
            'name' => $validated['name'],
            'bust' => $validated['bust'] ?? null,
            'waist' => $validated['waist'] ?? null,
            'hips' => $validated['hips'] ?? null,
            'length' => $validated['length'] ?? null,
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', "Size '{$validated['name']}' added to size guide chart.");
    }

    public function update(StoreSizeGuideRequest $request, SizeGuide $sizeGuide)
    {
        $validated = $request->validated();
        $sizeGuide->update($validated);

        return back()->with('success', "Size '{$sizeGuide->name}' updated successfully.");
    }

    public function toggle(SizeGuide $sizeGuide)
    {
        $sizeGuide->update(['is_active' => ! $sizeGuide->is_active]);

        return back()->with('success', "Size '{$sizeGuide->name}' status updated.");
    }

    public function destroy(SizeGuide $sizeGuide)
    {
        $sizeName = $sizeGuide->name;
        $sizeGuide->delete();

        return back()->with('success', "Size '{$sizeName}' removed from size guide.");
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'size_guide_title' => 'required|string|max:255',
            'size_guide_description' => 'nullable|string',
            'size_guide_unit' => 'required|string|max:50',
        ]);

        SystemSetting::set('size_guide_title', $validated['size_guide_title']);
        SystemSetting::set('size_guide_description', $validated['size_guide_description'] ?? '');
        SystemSetting::set('size_guide_unit', $validated['size_guide_unit']);

        return back()->with('success', 'Size guide title, unit, and how-to-measure description updated!');
    }
}
