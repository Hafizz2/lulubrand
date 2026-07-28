<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryImagesSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'dresses' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=600&q=80',
            'maxi-dresses' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=600&q=80',
            'mini-dresses' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=600&q=80',
            'corset-dresses' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=600&q=80',
            'outerwear-tailoring' => 'https://images.unsplash.com/photo-1548624313-0396c75e4b1a?auto=format&fit=crop&w=600&q=80',
            'casual-wear' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&w=600&q=80',
            'workwear' => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?auto=format&fit=crop&w=600&q=80',
            'seasonal-trends' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=600&q=80',
        ];

        foreach (Category::all() as $category) {
            if (isset($images[$category->slug])) {
                $category->update(['image_url' => $images[$category->slug]]);
            } elseif (empty($category->image_url)) {
                $category->update(['image_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=600&q=80']);
            }
        }
    }
}
