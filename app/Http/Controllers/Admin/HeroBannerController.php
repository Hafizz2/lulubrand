<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HeroBannerController extends Controller
{
    public function index()
    {
        $banners = HeroBanner::orderBy('sort_order')->orderByDesc('id')->get();

        return Inertia::render('HeroBanners/Index', [
            'banners' => $banners,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => ['required', 'string', 'max:255'],
            'subtitle'               => ['nullable', 'string', 'max:255'],
            'button_text'            => ['nullable', 'string', 'max:100'],
            'button_url'             => ['nullable', 'string', 'max:255'],
            'image_url'              => ['nullable', 'string'],
            'image_file'             => ['nullable', 'file', 'image', 'max:10240'],
            'desktop_focal_position' => ['nullable', 'string', 'max:100'],
            'mobile_focal_position'  => ['nullable', 'string', 'max:100'],
            'is_active'              => ['boolean'],
            'sort_order'             => ['integer'],
        ]);

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('hero_banners', 'public');
            $imageUrl = '/storage/' . $path;
        }

        HeroBanner::create([
            'title'                  => $validated['title'],
            'subtitle'               => $validated['subtitle'] ?? null,
            'button_text'            => $validated['button_text'] ?? 'SHOP COLLECTION',
            'button_url'             => $validated['button_url'] ?? '/categories',
            'image_url'              => $imageUrl ?? 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1600&q=80',
            'desktop_focal_position' => $validated['desktop_focal_position'] ?? 'center center',
            'mobile_focal_position'  => $validated['mobile_focal_position'] ?? 'center center',
            'is_active'              => $validated['is_active'] ?? true,
            'sort_order'             => $validated['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Hero Banner created successfully.');
    }

    public function update(Request $request, HeroBanner $banner)
    {
        $validated = $request->validate([
            'title'                  => ['required', 'string', 'max:255'],
            'subtitle'               => ['nullable', 'string', 'max:255'],
            'button_text'            => ['nullable', 'string', 'max:100'],
            'button_url'             => ['nullable', 'string', 'max:255'],
            'image_url'              => ['nullable', 'string'],
            'image_file'             => ['nullable', 'file', 'image', 'max:10240'],
            'desktop_focal_position' => ['nullable', 'string', 'max:100'],
            'mobile_focal_position'  => ['nullable', 'string', 'max:100'],
            'is_active'              => ['boolean'],
            'sort_order'             => ['integer'],
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('hero_banners', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        $banner->update($validated);

        return back()->with('success', 'Hero Banner updated successfully.');
    }

    public function destroy(HeroBanner $banner)
    {
        $banner->delete();
        return back()->with('success', 'Hero Banner deleted.');
    }
}
