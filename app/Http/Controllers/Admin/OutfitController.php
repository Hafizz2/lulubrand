<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOutfitRequest;
use App\Http\Requests\Admin\UpdateOutfitRequest;
use App\Models\Outfit;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OutfitController extends Controller
{
    /**
     * Display a listing of outfits.
     */
    public function index(Request $request): Response
    {
        $outfits = Outfit::orderByDesc('id')->paginate(12);

        // Fetch a lightweight list of published products for the select dropdown
        $products = Product::where('status', 'published')
            ->with(['primaryImage'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'product_code' => $product->product_code,
                    'image_url' => $product->primaryImage ? $product->primaryImage->url : url('/logo.png'),
                ];
            });

        return Inertia::render('Outfits/Index', [
            'outfits' => $outfits,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created outfit in storage.
     */
    public function store(StoreOutfitRequest $request)
    {
        $validated = $request->validated();
        $imagePath = null;

        // Handle base64 compressed data URL or actual file
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('outfits', 'public');
            $imagePath = '/storage/' . $path;
        } elseif (!empty($validated['image_url'])) {
            $url = $validated['image_url'];
            if (str_starts_with($url, 'data:image/')) {
                preg_match('/data:image\/(.*?);base64,(.*)/', $url, $matches);
                $extension = $matches[1] ?? 'jpg';
                $fileData = base64_decode($matches[2] ?? '');
                $fileName = 'outfits/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $imagePath = '/storage/' . $fileName;
            } else {
                $imagePath = $url;
            }
        }

        $galleryImages = [];
        
        if ($request->hasFile('images_files')) {
            foreach ($request->file('images_files') as $file) {
                $path = $file->store('outfits', 'public');
                $galleryImages[] = '/storage/' . $path;
            }
        }
        
        if (!empty($validated['images_urls'])) {
            foreach ($validated['images_urls'] as $url) {
                if (empty($url)) continue;
                if (str_starts_with($url, 'data:image/')) {
                    preg_match('/data:image\/(.*?);base64,(.*)/', $url, $matches);
                    $extension = $matches[1] ?? 'jpg';
                    $fileData = base64_decode($matches[2] ?? '');
                    $fileName = 'outfits/' . Str::uuid() . '.' . $extension;
                    Storage::disk('public')->put($fileName, $fileData);
                    $galleryImages[] = '/storage/' . $fileName;
                } else {
                    $galleryImages[] = $url;
                }
            }
        }

        Outfit::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'images' => $galleryImages,
            'status' => $validated['status'],
            'product_ids' => $validated['product_ids'] ?? [],
        ]);

        return back()->with('success', 'Outfit created successfully.');
    }

    /**
     * Update the specified outfit in storage.
     */
    public function update(UpdateOutfitRequest $request, Outfit $outfit)
    {
        $validated = $request->validated();
        $imagePath = $outfit->image_path;

        if ($request->hasFile('image_file')) {
            // Delete old file if exists in storage
            if ($outfit->image_path && str_starts_with($outfit->image_path, '/storage/outfits/')) {
                $oldFile = str_replace('/storage/', '', $outfit->image_path);
                Storage::disk('public')->delete($oldFile);
            }
            $path = $request->file('image_file')->store('outfits', 'public');
            $imagePath = '/storage/' . $path;
        } elseif (!empty($validated['image_url'])) {
            $url = $validated['image_url'];
            if (str_starts_with($url, 'data:image/')) {
                // Delete old file if exists
                if ($outfit->image_path && str_starts_with($outfit->image_path, '/storage/outfits/')) {
                    $oldFile = str_replace('/storage/', '', $outfit->image_path);
                    Storage::disk('public')->delete($oldFile);
                }
                preg_match('/data:image\/(.*?);base64,(.*)/', $url, $matches);
                $extension = $matches[1] ?? 'jpg';
                $fileData = base64_decode($matches[2] ?? '');
                $fileName = 'outfits/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $imagePath = '/storage/' . $fileName;
            } else {
                $imagePath = $url;
            }
        }

        $galleryImages = [];
        
        // Retain existing images
        if (!empty($validated['images_urls'])) {
            foreach ($validated['images_urls'] as $url) {
                if (empty($url)) continue;
                if (!str_starts_with($url, 'data:image/')) {
                    $galleryImages[] = $url;
                }
            }
        }

        // Upload new files
        if ($request->hasFile('images_files')) {
            foreach ($request->file('images_files') as $file) {
                $path = $file->store('outfits', 'public');
                $galleryImages[] = '/storage/' . $path;
            }
        }

        // Upload new base64 data URLs
        if (!empty($validated['images_urls'])) {
            foreach ($validated['images_urls'] as $url) {
                if (empty($url)) continue;
                if (str_starts_with($url, 'data:image/')) {
                    preg_match('/data:image\/(.*?);base64,(.*)/', $url, $matches);
                    $extension = $matches[1] ?? 'jpg';
                    $fileData = base64_decode($matches[2] ?? '');
                    $fileName = 'outfits/' . Str::uuid() . '.' . $extension;
                    Storage::disk('public')->put($fileName, $fileData);
                    $galleryImages[] = '/storage/' . $fileName;
                }
            }
        }

        // Clean up deleted files from disk
        $oldImages = $outfit->images ?? [];
        $deletedImages = array_diff($oldImages, $galleryImages);
        foreach ($deletedImages as $deletedImage) {
            if (str_starts_with($deletedImage, '/storage/outfits/')) {
                $oldFile = str_replace('/storage/', '', $deletedImage);
                Storage::disk('public')->delete($oldFile);
            }
        }

        $outfit->update([
            'name' => $validated['name'],
            'slug' => $outfit->name !== $validated['name'] ? (Str::slug($validated['name']) . '-' . Str::random(4)) : $outfit->slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'images' => $galleryImages,
            'status' => $validated['status'],
            'product_ids' => $validated['product_ids'] ?? [],
        ]);

        return back()->with('success', 'Outfit updated successfully.');
    }

    /**
     * Remove the specified outfit from storage.
     */
    public function destroy(Outfit $outfit)
    {
        // Delete image file if exists in storage
        if ($outfit->image_path && str_starts_with($outfit->image_path, '/storage/outfits/')) {
            $oldFile = str_replace('/storage/', '', $outfit->image_path);
            Storage::disk('public')->delete($oldFile);
        }

        // Delete gallery image files if they exist in storage
        $oldImages = $outfit->images ?? [];
        foreach ($oldImages as $image) {
            if (str_starts_with($image, '/storage/outfits/')) {
                $oldFile = str_replace('/storage/', '', $image);
                Storage::disk('public')->delete($oldFile);
            }
        }

        $outfit->delete();

        return back()->with('success', 'Outfit deleted successfully.');
    }
}
