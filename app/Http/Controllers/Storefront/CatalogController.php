<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\Outfit;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home()
    {
        $heroBanners = \App\Models\HeroBanner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredCategories = Category::whereNull('parent_id')
            ->with(['children'])
            ->orderBy('sort_order')
            ->get();

        $newArrivals = Product::where('status', 'published')
            ->where('is_new', true)
            ->with(['primaryImage', 'images', 'category', 'variants.attributeValues.attribute', 'variants.image'])
            ->latest()
            ->take(4)
            ->get();

        // Active discount for announcement bar — only show bar if there's a live promo or custom message
        $activeDiscount = Discount::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        $announcementMessage = SystemSetting::get('announcement_message', '');

        return view('storefront.home', compact(
            'heroBanners',
            'featuredCategories',
            'newArrivals',
            'activeDiscount',
            'announcementMessage'
        ));
    }

    public function categoriesIndex()
    {
        $categories = Category::whereNull('parent_id')
            ->with(['children', 'products'])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('storefront.categories.index', compact('categories'));
    }

    public function index(Request $request, ?string $slug = null)
    {
        $currentCategory = null;
        if ($slug) {
            $currentCategory = Category::where('slug', $slug)->firstOrFail();
        }

        $categories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = Attribute::with('values')->get();

        // Build Product Query
        $query = Product::where('status', 'published')
            ->with(['primaryImage', 'images', 'category', 'variants.attributeValues.attribute', 'variants.image']);

        // Search Query Filter
        if ($request->filled('q')) {
            $searchTerm = trim($request->input('q'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('material', 'like', "%{$searchTerm}%");
            });
        }

        // Category Filter
        if ($currentCategory) {
            $categoryIds = array_merge([$currentCategory->id], $currentCategory->children->pluck('id')->toArray());
            $query->whereIn('category_id', $categoryIds);
        } elseif ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // Size & Colour Attribute Value Filters
        if ($request->filled('sizes')) {
            $sizeValues = is_array($request->input('sizes')) ? $request->input('sizes') : explode(',', $request->input('sizes'));
            $query->whereHas('variants.attributeValues', function ($q) use ($sizeValues) {
                $q->whereIn('value', $sizeValues);
            });
        }

        if ($request->filled('colors')) {
            $colorValues = is_array($request->input('colors')) ? $request->input('colors') : explode(',', $request->input('colors'));
            $query->whereHas('variants.attributeValues', function ($q) use ($colorValues) {
                $q->whereIn('value', $colorValues);
            });
        }

        // In Stock Only Filter
        if ($request->boolean('in_stock')) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
        }

        // Min & Max Price Filter (inputs in major currency units, DB stores minor units)
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', (int) $request->input('min_price') * 100);
        }

        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', (int) $request->input('max_price') * 100);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'price_asc') {
            $query->orderBy('base_price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('base_price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'products' => $products->items(),
                'has_more' => $products->hasMorePages(),
                'next_page' => $products->currentPage() + 1,
                'total' => $products->total(),
                'html' => view('storefront.partials.product_grid', compact('products'))->render(),
            ]);
        }

        return view('storefront.catalog.index', compact('products', 'categories', 'attributes', 'currentCategory', 'sort'));
    }

    public function show(string $product_code)
    {
        // Look up by product_code (URL) — fallback to slug for backward compat
        $product = Product::where('product_code', $product_code)
            ->where('status', 'published')
            ->with(['primaryImage', 'images', 'category', 'variants.attributeValues.attribute', 'variants.image'])
            ->first();

        // Backwards-compatible fallback — support old slug-based URLs
        if (! $product) {
            $product = Product::where('slug', $product_code)
                ->where('status', 'published')
                ->with(['primaryImage', 'images', 'category', 'variants.attributeValues.attribute', 'variants.image'])
                ->firstOrFail();
        }

        $relatedProducts = collect();
        if (!empty($product->related_product_codes)) {
            $codes = array_map('trim', explode(',', $product->related_product_codes));
            $relatedProducts = Product::whereIn('product_code', $codes)
                ->where('status', 'published')
                ->with(['primaryImage', 'images', 'variants.attributeValues.attribute'])
                ->get();
        }
        
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 'published')
                ->with(['primaryImage', 'images', 'variants.attributeValues.attribute'])
                ->take(4)
                ->get();
        }

        // Fetch bundle products if this is an outfit
        $bundleProducts = collect();
        if (!empty($product->bundle_product_codes)) {
            $bundleCodes = array_map('trim', explode(',', $product->bundle_product_codes));
            $bundleProducts = Product::whereIn('product_code', $bundleCodes)
                ->where('status', 'published')
                ->with(['primaryImage', 'images', 'category', 'variants.attributeValues.attribute', 'variants.image'])
                ->get();
        }

        // Extract available sizes & colours for variant picker
        $attributesData = [];
        foreach ($product->variants as $variant) {
            foreach ($variant->attributeValues as $val) {
                $attrName = $val->attribute->name;
                if (! isset($attributesData[$attrName])) {
                    $attributesData[$attrName] = [];
                }
                $attributesData[$attrName][$val->id] = [
                    'id'         => $val->id,
                    'value'      => $val->value,
                    'color_code' => $val->color_code,
                ];
            }
        }

        // Prepare clean variants & images array for frontend JS
        $variantsJson = $product->variants->map(function ($v) use ($product) {
            return [
                'id'    => $v->id,
                'sku'   => $v->sku,
                'stock' => $v->stock_quantity,
                'price' => number_format(($v->price_override ?? $product->base_price) / 100, 2),
                'image' => $v->image ? $v->image->url : null,
                'attrs' => $v->attributeValues->pluck('value')->toArray(),
            ];
        })->toArray();

        $imagesJson = $product->images->map(function ($img) {
            return [
                'id'          => $img->id,
                'url'         => $img->url,
                'color_value' => $img->color_value,
            ];
        })->toArray();

        // Size Guide Data & Settings
        $sizeGuides = \App\Models\SizeGuide::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $sizeGuideTitle       = SystemSetting::get('size_guide_title', 'LULU Couture Size Guide');
        $sizeGuideDescription = SystemSetting::get('size_guide_description', "How to measure your size accurately:\n\n• Bust: Measure around the fullest part of your bust.\n• Waist: Measure around your natural waistline.\n• Hips: Measure around the fullest part of your hips.");
        $sizeGuideUnit        = SystemSetting::get('size_guide_unit', 'Inches (in)');

        return view('storefront.catalog.show', compact(
            'product',
            'relatedProducts',
            'bundleProducts',
            'attributesData',
            'variantsJson',
            'imagesJson',
            'sizeGuides',
            'sizeGuideTitle',
            'sizeGuideDescription',
            'sizeGuideUnit'
        ));
    }

    /**
     * Show details of a specific outfit look.
     */
    public function showOutfit(string $slug)
    {
        $outfit = Outfit::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $bundleProducts = $outfit->products;

        // Size Guide Data & Settings
        $sizeGuides = \App\Models\SizeGuide::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $sizeGuideTitle       = SystemSetting::get('size_guide_title', 'LULU Couture Size Guide');
        $sizeGuideDescription = SystemSetting::get('size_guide_description', "How to measure your size accurately:\n\n• Bust: Measure around the fullest part of your bust.\n• Waist: Measure around your natural waistline.\n• Hips: Measure around the fullest part of your hips.");
        $sizeGuideUnit        = SystemSetting::get('size_guide_unit', 'Inches (in)');

        return view('storefront.catalog.outfit', compact(
            'outfit',
            'bundleProducts',
            'sizeGuides',
            'sizeGuideTitle',
            'sizeGuideDescription',
            'sizeGuideUnit'
        ));
    }
}
