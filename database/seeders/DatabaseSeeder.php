<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User (Owner)
        $admin = User::updateOrCreate(
            ['email' => 'admin@lulu.com'],
            [
                'name' => 'LULU Brand Owner',
                'phone' => '+251911223344',
                'role' => 'owner',
                'password' => bcrypt('password'),
            ]
        );

        // 1b. Call Checkout System Seeder
        $this->call(CheckoutSystemSeeder::class);
        $this->call(SizeGuideSeeder::class);

        // 2. Create Attributes & Values
        $sizeAttr = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Size']);
        $sizes = [];
        foreach (['XS', 'S', 'M', 'L', 'XL'] as $sizeVal) {
            $sizes[$sizeVal] = AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttr->id,
                'value' => $sizeVal,
            ]);
        }

        $colorAttr = Attribute::firstOrCreate(['slug' => 'colour'], ['name' => 'Colour']);
        $colorsData = [
            'Midnight Black' => '#09090b',
            'Ivory White' => '#f8fafc',
            'Ruby Red' => '#991b1b',
            'Champagne Gold' => '#fef08a',
            'Emerald Green' => '#065f46',
        ];
        $colors = [];
        foreach ($colorsData as $cName => $cHex) {
            $colors[$cName] = AttributeValue::firstOrCreate([
                'attribute_id' => $colorAttr->id,
                'value' => $cName,
            ], [
                'color_code' => $cHex,
            ]);
        }

        // 3. Create Categories with High-Fashion Image URLs
        $dressesCategory = Category::firstOrCreate(['slug' => 'dresses'], [
            'name' => 'Dresses',
            'description' => 'Luxury evening and cocktail dresses',
            'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 1,
        ]);

        $maxiDresses = Category::firstOrCreate(['slug' => 'maxi-dresses'], [
            'parent_id' => $dressesCategory->id,
            'name' => 'Maxi Dresses',
            'description' => 'Floor-length gowns and maxi silhouettes',
            'image_url' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 1,
        ]);

        $miniDresses = Category::firstOrCreate(['slug' => 'mini-dresses'], [
            'parent_id' => $dressesCategory->id,
            'name' => 'Mini Dresses',
            'description' => 'Sculpted statement mini dresses',
            'image_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 2,
        ]);

        $corsetDresses = Category::firstOrCreate(['slug' => 'corset-dresses'], [
            'parent_id' => $dressesCategory->id,
            'name' => 'Corset Dresses',
            'description' => 'Couture corsetry and structured waists',
            'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 3,
        ]);

        $outerwearCategory = Category::firstOrCreate(['slug' => 'outerwear-tailoring'], [
            'name' => 'Outerwear & Tailoring',
            'description' => 'Blazers, coats, and tailored jackets',
            'image_url' => 'https://images.unsplash.com/photo-1548624313-0396c75e4b1a?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 2,
        ]);

        Category::firstOrCreate(['slug' => 'casual-wear'], [
            'name' => 'Casual Wear',
            'description' => 'Chic Everyday Knits & Denim',
            'image_url' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 3,
        ]);

        Category::firstOrCreate(['slug' => 'workwear'], [
            'name' => 'Workwear',
            'description' => 'Polished Suiting & Tailored Sets',
            'image_url' => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 4,
        ]);

        Category::firstOrCreate(['slug' => 'seasonal-trends'], [
            'name' => 'Seasonal Trends',
            'description' => 'Editorial Spring/Summer Collections',
            'image_url' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 5,
        ]);

        // 4. Create 15 Demo Products
        $demoProducts = [
            ['title' => 'Seraphina Corset Satin Gown', 'price' => 18900, 'cat' => $corsetDresses],
            ['title' => 'Aurelia Draped Silk Maxi Dress', 'price' => 21000, 'cat' => $maxiDresses],
            ['title' => 'Celeste Bodycon Velvet Mini', 'price' => 14500, 'cat' => $miniDresses],
            ['title' => 'Genevieve Sculpted Blazer Coat', 'price' => 24000, 'cat' => $outerwearCategory],
            ['title' => 'Vivienne Halterneck Lace Maxi', 'price' => 19500, 'cat' => $maxiDresses],
            ['title' => 'Rosalia Off-Shoulder Crepe Gown', 'price' => 22500, 'cat' => $maxiDresses],
            ['title' => 'Isabella Strapless Satin Mini', 'price' => 13900, 'cat' => $miniDresses],
            ['title' => 'Evangeline Mesh Overlay Maxi', 'price' => 17500, 'cat' => $maxiDresses],
            ['title' => 'Clarissa Structured Waist Corset', 'price' => 16500, 'cat' => $corsetDresses],
            ['title' => 'Ophelia Double-Breasted Satin Blazer', 'price' => 19900, 'cat' => $outerwearCategory],
            ['title' => 'Luciana Backless Plunging Gown', 'price' => 23000, 'cat' => $maxiDresses],
            ['title' => 'Valentina Cut-Out Bandit Dress', 'price' => 15500, 'cat' => $miniDresses],
            ['title' => 'Juliet Sheer Ruched Bodycon', 'price' => 14900, 'cat' => $miniDresses],
            ['title' => 'Anastasia Embellished Prom Dress', 'price' => 26000, 'cat' => $dressesCategory],
            ['title' => 'Dominique Tailored Tuxedo Jacket', 'price' => 21500, 'cat' => $outerwearCategory],
        ];

        $sizeKeys = array_keys($sizes);
        $colorKeys = array_keys($colors);

        foreach ($demoProducts as $idx => $pData) {
            $slug = Str::slug($pData['title']);
            $product = Product::firstOrCreate(['slug' => $slug], [
                'category_id' => $pData['cat']->id,
                'title' => $pData['title'],
                'description' => "Crafted from premium fabrics with meticulous attention to detail. Designed for a sleek, contoured fit.",
                'base_price' => $pData['price'],
                'status' => 'published',
                'is_new' => $idx < 6,
                'published_at' => now(),
            ]);

            // Add 2 Images per Product if none exist
            if ($product->images()->count() === 0) {
                $primaryImg = ProductImage::create([
                    'product_id' => $product->id,
                    'url' => "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80",
                    'sort_order' => 1,
                    'is_primary' => true,
                ]);

                $secondaryImg = ProductImage::create([
                    'product_id' => $product->id,
                    'url' => "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80",
                    'sort_order' => 2,
                    'is_primary' => false,
                ]);
            } else {
                $primaryImg = $product->primaryImage;
                $secondaryImg = $product->images()->where('is_primary', false)->first() ?? $primaryImg;
            }

            // Create 3 Variants for each Product if none exist
            if ($product->variants()->count() === 0) {
                $variantCombinations = [
                    [$sizeKeys[1], $colorKeys[0]], // S / Midnight Black
                    [$sizeKeys[2], $colorKeys[1]], // M / Ivory White
                    [$sizeKeys[2], $colorKeys[2]], // M / Ruby Red
                ];

                foreach ($variantCombinations as $vIdx => $combo) {
                    $sizeVal = $sizes[$combo[0]];
                    $colorVal = $colors[$combo[1]];

                    $sku = strtoupper(Str::slug($product->title)) . '-' . $sizeVal->value . '-' . substr(Str::slug($colorVal->value), 0, 3);
                    $stock = rand(5, 25);

                    $variant = ProductVariant::firstOrCreate(['sku' => $sku], [
                        'product_id' => $product->id,
                        'stock_quantity' => $stock,
                        'image_id' => ($vIdx % 2 === 0) ? $primaryImg->id : $secondaryImg->id,
                    ]);

                    // Attach Attribute Values to Pivot
                    $variant->attributeValues()->syncWithoutDetaching([$sizeVal->id, $colorVal->id]);

                    // Audit Stock Movement
                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'delta' => $stock,
                        'resulting_quantity' => $stock,
                        'reason' => 'initial_seed',
                        'actor_id' => $admin->id,
                    ]);
                }
            }
        }
    }
}
