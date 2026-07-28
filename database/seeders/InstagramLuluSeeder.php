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

class InstagramLuluSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'owner')->first() ?? User::first();

        // Size & Color attributes
        $sizeAttr = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Size']);
        $colorAttr = Attribute::firstOrCreate(['slug' => 'colour'], ['name' => 'Colour']);

        $sizes = [];
        foreach (['XS', 'S', 'M', 'L', 'XL'] as $sVal) {
            $sizes[$sVal] = AttributeValue::firstOrCreate(['attribute_id' => $sizeAttr->id, 'value' => $sVal]);
        }

        $colorsData = [
            'Midnight Black' => '#09090b',
            'Ivory Gold' => '#fef08a',
            'Royal Emerald' => '#065f46',
            'Crimson Ruby' => '#991b1b',
            'Dusty Rose' => '#c49a9a',
        ];
        $colors = [];
        foreach ($colorsData as $cName => $cHex) {
            $colors[$cName] = AttributeValue::firstOrCreate([
                'attribute_id' => $colorAttr->id,
                'value' => $cName,
            ], ['color_code' => $cHex]);
        }

        // Categories
        $dressesCat = Category::firstOrCreate(['slug' => 'dresses'], [
            'name' => 'Dresses',
            'description' => 'High-fashion habesha couture and cocktail dresses',
            'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=800&q=80',
        ]);

        $corsetCat = Category::firstOrCreate(['slug' => 'corset-dresses'], [
            'name' => 'Corset Dresses',
            'description' => 'Sculpted corsetry and structured waists',
            'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80',
        ]);

        $outerwearCat = Category::firstOrCreate(['slug' => 'outerwear-tailoring'], [
            'name' => 'Outerwear & Tailoring',
            'description' => 'Blazers and tailored luxury sets',
            'image_url' => 'https://images.unsplash.com/photo-1548624313-0396c75e4b1a?auto=format&fit=crop&w=800&q=80',
        ]);

        // 7 Authentic @lulu__addis Instagram Inspired Products
        $instaProducts = [
            [
                'title' => 'The Zewditu Habesha Silk Gown',
                'price' => 26000, // $260.00
                'category' => $dressesCat,
                'material' => 'Pure Mulberry Silk & Gold Thread',
                'description' => 'Authentic @lulu__addis signature couture. Hand-woven silk with gold metallic thread embroidery, contoured waistline, and sweeping maxi silhouette.',
                'image_primary' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Abyssinian Velvet Corset Mini',
                'price' => 19500, // $195.00
                'category' => $corsetCat,
                'material' => 'Deep Plush Velvet & Boning',
                'description' => 'Sculpted velvet corsetry featuring sheer mesh side panels, internal waist boning, and dramatic off-shoulder drape.',
                'image_primary' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Queen Taitu Draped Satin Maxi',
                'price' => 22000, // $220.00
                'category' => $dressesCat,
                'material' => 'Heavyweight Champagne Satin',
                'description' => 'High-neck liquid satin maxi dress with a dramatic back cape train and fitted hip contour.',
                'image_primary' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Semira Ruched Chiffon Cocktail Dress',
                'price' => 17500, // $175.00
                'category' => $dressesCat,
                'material' => 'Silk Chiffon & Micro Beads',
                'description' => 'Ruched chiffon overlay dress with delicate Ethiopian micro-beading along the neckline and corset back.',
                'image_primary' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Addis Luxury Tailored Blazer Set',
                'price' => 24000, // $240.00
                'category' => $outerwearCat,
                'material' => 'Structured Crepe & Satin Lapels',
                'description' => 'Double-breasted tailored blazer jacket with satin lapels and matching high-waisted wide-leg trousers.',
                'image_primary' => 'https://images.unsplash.com/photo-1548624313-0396c75e4b1a?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Aster Emerald Halter Silk Gown',
                'price' => 28000, // $280.00
                'category' => $dressesCat,
                'material' => '100% Pure Mulberry Silk',
                'description' => 'Backless halter silk gown in deep royal emerald green with front thigh-high slit and cascading train.',
                'image_primary' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Lidiya Sculpted Off-Shoulder Midi',
                'price' => 21000, // $210.00
                'category' => $corsetCat,
                'material' => 'Heavy Stretch Satin & Boning',
                'description' => 'Contoured off-shoulder midi dress featuring built-in waist corset, sweetheart neckline, and back zip closure.',
                'image_primary' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&w=900&q=80',
                'image_secondary' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        foreach ($instaProducts as $pData) {
            $slug = Str::slug($pData['title']);
            $product = Product::updateOrCreate(['slug' => $slug], [
                'category_id' => $pData['category']->id,
                'title' => $pData['title'],
                'description' => $pData['description'],
                'material' => $pData['material'],
                'base_price' => $pData['price'],
                'status' => 'published',
                'is_new' => true,
                'published_at' => now(),
            ]);

            // Images
            ProductImage::where('product_id', $product->id)->delete();

            $pImg = ProductImage::create([
                'product_id' => $product->id,
                'url' => $pData['image_primary'],
                'sort_order' => 1,
                'is_primary' => true,
                'color_value' => 'Midnight Black',
            ]);

            $sImg = ProductImage::create([
                'product_id' => $product->id,
                'url' => $pData['image_secondary'],
                'sort_order' => 2,
                'is_primary' => false,
                'color_value' => 'Ivory Gold',
            ]);

            // Create Variants for sizes & colors
            if ($product->variants()->count() === 0) {
                $combos = [
                    ['S', 'Midnight Black', 12, $pImg->id],
                    ['M', 'Ivory Gold', 10, $sImg->id],
                    ['L', 'Royal Emerald', 8, $pImg->id],
                ];

                foreach ($combos as $combo) {
                    $sVal = $sizes[$combo[0]];
                    $cVal = $colors[$combo[1]];
                    $sku = strtoupper(Str::slug($product->title)) . '-' . $sVal->value . '-' . substr(Str::slug($cVal->value), 0, 3);

                    $variant = ProductVariant::firstOrCreate(['sku' => $sku], [
                        'product_id' => $product->id,
                        'stock_quantity' => $combo[2],
                        'image_id' => $combo[3],
                    ]);

                    $variant->attributeValues()->syncWithoutDetaching([$sVal->id, $cVal->id]);

                    if ($admin) {
                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'delta' => $combo[2],
                            'resulting_quantity' => $combo[2],
                            'reason' => 'insta_seed',
                            'actor_id' => $admin->id,
                        ]);
                    }
                }
            }
        }
    }
}
