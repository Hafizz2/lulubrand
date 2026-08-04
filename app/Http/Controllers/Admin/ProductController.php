<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkProductRequest;
use App\Http\Requests\Admin\QuickUpdateProductRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'primaryImage', 'variants.attributeValues'])
            ->withCount('variants');

        // Search Filter (Title, Slug, or SKU)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        
        // Ensure size attribute exists and values match size guide
        $sizeAttr = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Size']);
        $activeSizes = \App\Models\SizeGuide::where('is_active', true)->orderBy('sort_order')->get();
        $sizeValues = [];
        foreach ($activeSizes as $sg) {
            $sizeValues[] = \App\Models\AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttr->id,
                'value' => $sg->name,
            ]);
        }

        $attributes = Attribute::with('values')->get()->map(function ($attr) use ($sizeAttr, $sizeValues) {
            if ($attr->id === $sizeAttr->id) {
                $attr->setRelation('values', collect($sizeValues));
            }
            return $attr;
        });

        $allProducts = Product::where('status', 'published')
            ->with(['primaryImage'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'product_code' => $product->product_code,
                    'image_url' => $product->primaryImage ? $product->primaryImage->url : 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80',
                ];
            });

        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'attributes' => $attributes,
            'allProducts' => $allProducts,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {
            $basePriceCents = (int) round($validated['base_price'] * 100);

            $relatedCodes = null;
            if (!empty($validated['related_product_ids'])) {
                $relatedCodes = Product::whereIn('id', $validated['related_product_ids'])
                    ->pluck('product_code')
                    ->filter()
                    ->implode(', ');
            }
            $relatedCodes = $relatedCodes ?: ($validated['related_product_codes'] ?? null);

            // 1. Create Product
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'product_code' => $validated['product_code'] ?? null,
                'related_product_codes' => $relatedCodes,
                'bundle_product_codes' => $validated['bundle_product_codes'] ?? null,
                'slug' => Str::slug($validated['title']) . '-' . Str::random(4),
                'description' => $validated['description'] ?? null,
                'material' => $validated['material'] ?? null,
                'base_price' => $basePriceCents,
                'status' => $validated['status'],
                'is_new' => $validated['is_new'] ?? true,
                'published_at' => $validated['status'] === 'published' ? now() : null,
            ]);

            // 2. Save Images (Handling uploaded files or compressed data URLs + Color tagging)
            $imageModels = [];
            if ($request->hasFile('image_files')) {
                foreach ($request->file('image_files') as $idx => $file) {
                    $path = $file->store('products', 'public');
                    $url = '/storage/' . $path;
                    $colorVal = $request->input("image_colors.{$idx}") ?? null;
                    $imageModels[] = ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $url,
                        'color_value' => $colorVal,
                        'sort_order' => $idx + 1,
                        'is_primary' => $idx === 0,
                    ]);
                }
            } elseif (! empty($validated['images'])) {
                foreach ($validated['images'] as $idx => $imgData) {
                    $url = is_array($imgData) ? ($imgData['url'] ?? '') : $imgData;
                    $colorVal = is_array($imgData) ? ($imgData['color_value'] ?? null) : null;
                    if (str_starts_with($url, 'data:image/')) {
                        preg_match('/data:image\/(.*?);base64,(.*)/', $url, $matches);
                        $extension = $matches[1] ?? 'jpg';
                        $fileData = base64_decode($matches[2] ?? '');
                        $fileName = 'products/' . Str::uuid() . '.' . $extension;
                        Storage::disk('public')->put($fileName, $fileData);
                        $url = '/storage/' . $fileName;
                    }
                    $imageModels[] = ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $url,
                        'color_value' => $colorVal,
                        'sort_order' => $idx + 1,
                        'is_primary' => $idx === 0,
                    ]);
                }
            } else {
                $imageModels[] = ProductImage::create([
                    'product_id' => $product->id,
                    'url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80',
                    'sort_order' => 1,
                    'is_primary' => true,
                ]);
            }

            $primaryImg = $imageModels[0] ?? null;

            // 3. Create Variants & Audit Stock Movements
            $colorAttr = null;
            foreach ($validated['variants'] as $vData) {
                $priceOverrideCents = ! empty($vData['price_override']) ? (int) round($vData['price_override'] * 100) : null;
                $stockQty = (int) $vData['stock_quantity'];

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper($vData['sku']),
                    'price_override' => $priceOverrideCents,
                    'stock_quantity' => $stockQty,
                    'image_id' => $primaryImg ? $primaryImg->id : null,
                ]);

                // Attach Attribute Values to Pivot
                if (! empty($vData['attribute_value_ids'])) {
                    $finalAttrIds = [];
                    foreach ($vData['attribute_value_ids'] as $attrId) {
                        if (is_numeric($attrId)) {
                            $intId = (int) $attrId;
                            if (\App\Models\AttributeValue::where('id', $intId)->exists()) {
                                $finalAttrIds[] = $intId;
                            }
                        } elseif (is_string($attrId) && ! empty($attrId)) {
                            if (! $colorAttr) {
                                $colorAttr = Attribute::firstOrCreate(['slug' => 'colour'], ['name' => 'Colour']);
                            }
                            $hex = str_contains($attrId, ':') ? explode(':', $attrId)[1] : (str_starts_with($attrId, '#') ? $attrId : '#8C6554');
                            $attrVal = \App\Models\AttributeValue::firstOrCreate([
                                'attribute_id' => $colorAttr->id,
                                'color_code' => $hex,
                            ], [
                                'value' => 'Custom ' . strtoupper($hex),
                            ]);
                            $finalAttrIds[] = $attrVal->id;
                        }
                    }

                    if (! empty($finalAttrIds)) {
                        $variant->attributeValues()->sync($finalAttrIds);
                    }
                }

                // Audit Initial Stock Movement
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'delta' => $stockQty,
                    'resulting_quantity' => $stockQty,
                    'reason' => 'product_created',
                    'actor_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product and variant matrix created successfully!');
    }

    public function edit(Product $product): Response
    {
        $product->load(['category', 'images', 'variants.attributeValues.attribute']);
        $categories = Category::whereNull('parent_id')->with('children')->get();
        
        // Ensure size attribute exists and values match size guide
        $sizeAttr = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Size']);
        $activeSizes = \App\Models\SizeGuide::where('is_active', true)->orderBy('sort_order')->get();
        $sizeValues = [];
        foreach ($activeSizes as $sg) {
            $sizeValues[] = \App\Models\AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttr->id,
                'value' => $sg->name,
            ]);
        }

        $attributes = Attribute::with('values')->get()->map(function ($attr) use ($sizeAttr, $sizeValues) {
            if ($attr->id === $sizeAttr->id) {
                $attr->setRelation('values', collect($sizeValues));
            }
            return $attr;
        });

        $allProducts = Product::where('status', 'published')
            ->with(['primaryImage'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'product_code' => $product->product_code,
                    'image_url' => $product->primaryImage ? $product->primaryImage->url : 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80',
                ];
            });

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'attributes' => $attributes,
            'allProducts' => $allProducts,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $product) {
            $relatedCodes = null;
            if (!empty($validated['related_product_ids'])) {
                $relatedCodes = Product::whereIn('id', $validated['related_product_ids'])
                    ->pluck('product_code')
                    ->filter()
                    ->implode(', ');
            }
            $relatedCodes = $relatedCodes ?: ($validated['related_product_codes'] ?? null);

            // 1. Update Core Product Details
            $product->update([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'product_code' => $validated['product_code'] ?? null,
                'related_product_codes' => $relatedCodes,
                'bundle_product_codes' => $validated['bundle_product_codes'] ?? null,
                'description' => $validated['description'] ?? null,
                'material' => $validated['material'] ?? null,
                'base_price' => (int) round($validated['base_price'] * 100),
                'status' => $validated['status'],
                'is_new' => $validated['is_new'] ?? false,
                'published_at' => $validated['status'] === 'published' ? ($product->published_at ?? now()) : null,
            ]);

            // 2. Handle Images Update if provided
            if (isset($validated['images'])) {
                $keptImageIds = [];
                foreach ($validated['images'] as $idx => $imgData) {
                    $url = is_array($imgData) ? ($imgData['url'] ?? '') : $imgData;
                    $colorVal = is_array($imgData) ? ($imgData['color_value'] ?? null) : null;
                    $isPrimary = $idx === 0;

                    if (str_starts_with($url, 'data:image/')) {
                        preg_match('/data:image\/(.*?);base64,(.*)/', $url, $matches);
                        $extension = $matches[1] ?? 'jpg';
                        if ($extension === 'jpeg') {
                            $extension = 'jpg';
                        }
                        $fileData = base64_decode($matches[2] ?? '');
                        $fileName = 'products/' . Str::uuid() . '.' . $extension;
                        Storage::disk('public')->put($fileName, $fileData);
                        $url = '/storage/' . $fileName;
                    }

                    if (! empty($imgData['id'])) {
                        $imgModel = ProductImage::find($imgData['id']);
                        if ($imgModel && $imgModel->product_id === $product->id) {
                            $imgModel->update([
                                'url' => $url,
                                'color_value' => $colorVal,
                                'sort_order' => $idx + 1,
                                'is_primary' => $isPrimary,
                            ]);
                            $keptImageIds[] = $imgModel->id;
                            continue;
                        }
                    }

                    $newImg = ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $url,
                        'color_value' => $colorVal,
                        'sort_order' => $idx + 1,
                        'is_primary' => $isPrimary,
                    ]);
                    $keptImageIds[] = $newImg->id;
                }

                if (! empty($keptImageIds)) {
                    ProductImage::where('product_id', $product->id)
                        ->whereNotIn('id', $keptImageIds)
                        ->delete();
                }
            }

            // 3. Handle Variants Update if provided
            if (isset($validated['variants'])) {
                $colorAttr = null;
                $keptVariantIds = [];

                foreach ($validated['variants'] as $vData) {
                    $priceOverrideCents = ! empty($vData['price_override']) ? (int) round($vData['price_override'] * 100) : null;
                    $stockQty = (int) $vData['stock_quantity'];
                    $variantId = $vData['id'] ?? null;

                    if ($variantId) {
                        $variant = ProductVariant::where('id', $variantId)->where('product_id', $product->id)->first();
                    } else {
                        $variant = null;
                    }

                    if ($variant) {
                        $delta = $stockQty - $variant->stock_quantity;
                        $variant->update([
                            'sku' => strtoupper($vData['sku']),
                            'price_override' => $priceOverrideCents,
                            'stock_quantity' => $stockQty,
                        ]);

                        if ($delta !== 0) {
                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'delta' => $delta,
                                'resulting_quantity' => $stockQty,
                                'reason' => 'product_updated',
                                'actor_id' => Auth::id(),
                            ]);
                        }
                    } else {
                        $primaryImg = $product->primaryImage;
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => strtoupper($vData['sku']),
                            'price_override' => $priceOverrideCents,
                            'stock_quantity' => $stockQty,
                            'image_id' => $primaryImg ? $primaryImg->id : null,
                        ]);

                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'delta' => $stockQty,
                            'resulting_quantity' => $stockQty,
                            'reason' => 'variant_added',
                            'actor_id' => Auth::id(),
                        ]);
                    }

                    $keptVariantIds[] = $variant->id;

                    if (isset($vData['attribute_value_ids'])) {
                        $finalAttrIds = [];
                        foreach ($vData['attribute_value_ids'] as $attrId) {
                            if (is_numeric($attrId)) {
                                $intId = (int) $attrId;
                                if (\App\Models\AttributeValue::where('id', $intId)->exists()) {
                                    $finalAttrIds[] = $intId;
                                }
                            } elseif (is_string($attrId) && ! empty($attrId)) {
                                if (! $colorAttr) {
                                    $colorAttr = Attribute::firstOrCreate(['slug' => 'colour'], ['name' => 'Colour']);
                                }
                                $hex = str_contains($attrId, ':') ? explode(':', $attrId)[1] : (str_starts_with($attrId, '#') ? $attrId : '#8C6554');
                                $attrVal = \App\Models\AttributeValue::firstOrCreate([
                                    'attribute_id' => $colorAttr->id,
                                    'color_code' => $hex,
                                ], [
                                    'value' => 'Custom ' . strtoupper($hex),
                                ]);
                                $finalAttrIds[] = $attrVal->id;
                            }
                        }

                        $variant->attributeValues()->sync($finalAttrIds);
                    }
                }

                if (! empty($keptVariantIds)) {
                    ProductVariant::where('product_id', $product->id)
                        ->whereNotIn('id', $keptVariantIds)
                        ->delete();
                }
            }
        });

        return back()->with('success', 'Product details, images, and size/color variants updated successfully!');
    }

    public function quickUpdate(QuickUpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        if (isset($validated['base_price'])) {
            $product->base_price = (int) round($validated['base_price'] * 100);
        }

        if (isset($validated['status'])) {
            $product->status = $validated['status'];
            if ($validated['status'] === 'published' && ! $product->published_at) {
                $product->published_at = now();
            }
        }

        $product->save();

        if (isset($validated['stock_quantity'])) {
            $firstVariant = $product->variants()->first();
            if ($firstVariant) {
                $delta = $validated['stock_quantity'] - $firstVariant->stock_quantity;
                $firstVariant->stock_quantity = $validated['stock_quantity'];
                $firstVariant->save();

                StockMovement::create([
                    'product_variant_id' => $firstVariant->id,
                    'delta' => $delta,
                    'resulting_quantity' => $validated['stock_quantity'],
                    'reason' => 'quick_edit',
                    'actor_id' => Auth::id(),
                ]);
            }
        }

        return back()->with('success', 'Quick update saved!');
    }

    public function bulk(BulkProductRequest $request)
    {
        $validated = $request->validated();

        if ($validated['action'] === 'publish') {
            Product::whereIn('id', $validated['ids'])->update(['status' => 'published', 'published_at' => now()]);
        } elseif ($validated['action'] === 'unpublish') {
            Product::whereIn('id', $validated['ids'])->update(['status' => 'draft']);
        } elseif ($validated['action'] === 'delete') {
            Product::whereIn('id', $validated['ids'])->delete();
        }

        return back()->with('success', "Bulk action '{$validated['action']}' completed on selected items.");
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
